<?php

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Account')] class extends Component {
    public string $first_name = '';

    public string $last_name = '';

    public string $username = '';

    public string $email = '';

    public string $emailPassword = '';

    public string $deletePassword = '';

    public bool $verificationSent = false;

    public function mount(): void
    {
        $user = Auth::user();
        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->username = $user->username;
        $this->email = $user->email;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:40', 'alpha_dash:ascii', Rule::unique(User::class)->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        // Email adalah identitas login dan tujuan reset password: perubahan wajib konfirmasi password.
        if ($emailChanged) {
            $this->validate(['emailPassword' => ['required', 'current_password']]);
        }

        $user->fill($validated);

        if ($emailChanged && $user instanceof MustVerifyEmail) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged && $user instanceof MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
        }

        $this->reset('emailPassword');
        $this->dispatch('profile-saved');
    }

    public function resendVerification(): void
    {
        $user = Auth::user();

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            $this->verificationSent = true;
        }
    }

    public function deleteAccount(): void
    {
        abort_unless(config('admin.account.deletable', true), 403);

        $this->validate(['deletePassword' => ['required', 'current_password']]);

        $user = Auth::user();

        Auth::logout();
        $user->delete();

        session()->invalidate();
        session()->regenerateToken();
        session()->flash('toast', ['type' => 'success', 'message' => 'Your account was deleted.']);

        $this->redirect('/');
    }
};
?>

<div class="space-y-4">
    <x-account.tabs />

    <x-ui.card :padded="false" class="divide-y divide-slate-100">
        <x-form.form-section title="Personal information" description="Your name and the email address you use to log in."
            saved="profile-saved" class="p-6 sm:p-8">
            <form id="profile-form" wire:submit="updateProfile" class="grid max-w-md gap-4">
                <div class="grid grid-cols-2 gap-3">
                    <x-form.input label="First name" name="first_name" wire:model="first_name" autocomplete="given-name"
                        required />
                    <x-form.input label="Last name" name="last_name" wire:model="last_name" autocomplete="family-name"
                        :optional="true" />
                </div>
                <x-form.input label="Username" name="username" wire:model="username" autocomplete="username" required />

                <x-form.input label="Email address" name="email" type="email" wire:model.live.blur="email"
                    autocomplete="email" required />

                @if ($email !== auth()->user()->email)
                    <x-form.input label="Current password" name="emailPassword" type="password"
                        wire:model="emailPassword" autocomplete="current-password" required />
                    <p class="-mt-2 text-xs text-slate-500">
                        Needed to change your email. You’ll be asked to verify the new address.
                    </p>
                @endif

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-800">
                        Your email address is not verified.
                        <button type="button" wire:click="resendVerification"
                            class="font-semibold underline underline-offset-2">Send a new link</button>
                        @if ($verificationSent)
                            <span class="block pt-1">A new link is on its way.</span>
                        @endif
                    </div>
                @endif
            </form>

            <x-slot:actions>
                <x-ui.button type="submit" form="profile-form" variant="dark" wire:loading.attr="disabled"
                    wire:target="updateProfile">Save changes</x-ui.button>
            </x-slot:actions>
        </x-form.form-section>

        @if (config('admin.account.deletable', true))
            <x-form.form-section title="Delete account" description="Permanently remove your account and its data."
                danger class="p-6 sm:p-8">
                <p class="text-sm leading-relaxed text-slate-600">
                    Once your account is deleted, you can’t log in again and it can’t be restored.
                </p>

                <x-slot:actions>
                    <x-ui.button variant="danger" @click="$dispatch('modal-open-delete')">Delete account</x-ui.button>
                </x-slot:actions>
            </x-form.form-section>
        @endif
    </x-ui.card>

    <x-ui.modal name="delete" title="Delete your account?" description="Enter your password to confirm.">
        <form wire:submit="deleteAccount" class="space-y-5">
            <x-form.input label="Password" name="deletePassword" type="password" wire:model="deletePassword"
                autocomplete="current-password" required />

            <div class="flex justify-end gap-2">
                <x-ui.button @click="$dispatch('modal-close-delete')">Cancel</x-ui.button>
                <x-ui.button type="submit" variant="danger" wire:loading.attr="disabled" wire:target="deleteAccount">
                    Delete account
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
