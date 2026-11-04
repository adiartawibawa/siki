<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Security')] class extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $code = '';

    public bool $showRecoveryCodes = false;

    public string $logoutPassword = '';

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()
            ->forceFill(['password' => Hash::make($this->password)])
            ->save();

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('password-saved');
    }

    public function enableTwoFactor(): void
    {
        if (! $this->recentlyConfirmed()) {
            return;
        }

        app(EnableTwoFactorAuthentication::class)(Auth::user());
        $this->showRecoveryCodes = false;
    }

    public function confirmTwoFactor(): void
    {
        if (! $this->recentlyConfirmed()) {
            return;
        }

        $this->validate(['code' => ['required', 'digits:6']]);

        app(ConfirmTwoFactorAuthentication::class)(Auth::user(), $this->code);

        $this->reset('code');
        $this->showRecoveryCodes = true;
    }

    public function regenerateRecoveryCodes(): void
    {
        if (! $this->recentlyConfirmed()) {
            return;
        }

        app(GenerateNewRecoveryCodes::class)(Auth::user());
        $this->showRecoveryCodes = true;
    }

    public function disableTwoFactor(): void
    {
        if (! $this->recentlyConfirmed()) {
            return;
        }

        app(DisableTwoFactorAuthentication::class)(Auth::user());
        $this->reset('code', 'showRecoveryCodes');
    }

    #[Computed]
    public function sessions(): array
    {
        if (config('session.driver') !== 'database') {
            return [];
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', Auth::id())
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($row) {
                $ua = $row->user_agent ?? '';

                return (object) [
                    'ip' => $row->ip_address,
                    'current' => $row->id === session()->getId(),
                    'last' => Carbon::createFromTimestamp($row->last_activity)->diffForHumans(),
                    'browser' => $this->browser($ua),
                    'os' => $this->os($ua),
                    'mobile' => Str::contains($ua, ['iPhone', 'Android', 'Mobile']),
                ];
            })
            ->all();
    }

    public function logoutOtherSessions(): void
    {
        $this->validate(['logoutPassword' => ['required', 'current_password']]);

        DB::table(config('session.table', 'sessions'))->where('user_id', Auth::id())->where('id', '!=', session()->getId())->delete();

        // Mencabut juga token "remember me" di perangkat lain.
        Auth::user()
            ->forceFill(['remember_token' => Str::random(60)])
            ->save();

        $this->reset('logoutPassword');
        $this->dispatch('modal-close-sessions');
        $this->dispatch('sessions-saved');
    }

    private function recentlyConfirmed(): bool
    {
        $confirmedAt = (int) session('auth.password_confirmed_at', 0);

        if (time() - $confirmedAt < config('auth.password_timeout', 10800)) {
            return true;
        }

        session(['url.intended' => route('account.security')]);
        $this->redirectRoute('password.confirm', navigate: true);

        return false;
    }

    private function browser(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/'), str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Safari/') => 'Safari',
            default => 'Unknown browser',
        };
    }

    private function os(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'iPhone'), str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Unknown OS',
        };
    }
};
?>

@php
    $user = auth()->user();
    $state = blank($user->two_factor_secret ?? null)
        ? 'off'
        : (blank($user->two_factor_confirmed_at ?? null)
            ? 'pending'
            : 'on');
@endphp

<div class="space-y-4">
    <x-account.tabs />

    <x-ui.card :padded="false" class="divide-y divide-slate-100">

        {{-- Password --}}
        <x-form.form-section title="Password" description="Use a long, unique password you don’t use anywhere else."
            saved="password-saved" class="p-6 sm:p-8">
            <form id="password-form" wire:submit="updatePassword" class="grid max-w-md gap-4">
                <x-form.input label="Current password" name="current_password" type="password"
                    wire:model="current_password" autocomplete="current-password" required />
                <x-form.input label="New password" name="password" type="password" wire:model="password"
                    autocomplete="new-password" required />
                <x-form.input label="Confirm new password" name="password_confirmation" type="password"
                    wire:model="password_confirmation" autocomplete="new-password" required />
            </form>

            <x-slot:actions>
                <x-ui.button type="submit" form="password-form" variant="dark" wire:loading.attr="disabled"
                    wire:target="updatePassword">Update password</x-ui.button>
            </x-slot:actions>
        </x-form.form-section>

        {{-- Two-factor --}}
        <x-form.form-section title="Two-factor authentication"
            description="Ask for a code from an authenticator app each time you log in." class="p-6 sm:p-8">

            @if ($state === 'off')
                <x-ui.badge tone="neutral">Not enabled</x-ui.badge>
                <p class="mt-3 max-w-prose text-sm leading-relaxed text-slate-600">
                    After you enable it, you’ll scan a QR code with an authenticator app and confirm a 6-digit code.
                </p>

                <x-slot:actions>
                    <x-ui.button variant="dark" wire:click="enableTwoFactor" wire:loading.attr="disabled"
                        wire:target="enableTwoFactor">Enable</x-ui.button>
                </x-slot:actions>
            @elseif ($state === 'pending')
                <x-ui.badge tone="warning">Setup not finished</x-ui.badge>

                <div class="mt-4 grid gap-6 sm:grid-cols-[auto_minmax(0,1fr)]">
                    <div class="w-fit rounded-2xl border border-slate-200 bg-white p-3">
                        {!! $user->twoFactorQrCodeSvg() !!}
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm leading-relaxed text-slate-600">
                            Scan the code with your authenticator app, then enter the 6-digit code it shows.
                            Can’t scan? Type this key instead:
                        </p>
                        <p
                            class="select-all break-all rounded-lg bg-slate-100 px-3 py-2 font-mono text-xs text-slate-800">
                            {{ decrypt($user->two_factor_secret) }}
                        </p>

                        <form id="confirm-2fa" wire:submit="confirmTwoFactor" class="max-w-xs">
                            <x-form.input label="Code from your app" name="code" wire:model="code"
                                inputmode="numeric" maxlength="6" autocomplete="one-time-code" required />
                        </form>
                    </div>
                </div>

                <x-slot:actions>
                    <x-ui.button wire:click="disableTwoFactor">Cancel</x-ui.button>
                    <x-ui.button type="submit" form="confirm-2fa" variant="dark" wire:loading.attr="disabled"
                        wire:target="confirmTwoFactor">Confirm</x-ui.button>
                </x-slot:actions>
            @else
                <x-ui.badge tone="success">Enabled</x-ui.badge>
                <p class="mt-3 max-w-prose text-sm leading-relaxed text-slate-600">
                    Keep your recovery codes somewhere safe. Each one works once if you lose access to your app.
                </p>

                @if ($showRecoveryCodes)
                    <ul
                        class="mt-4 grid max-w-sm gap-2 rounded-xl bg-slate-100 p-4 font-mono text-xs text-slate-800 sm:grid-cols-2">
                        @foreach ($user->recoveryCodes() as $recoveryCode)
                            <li class="select-all">{{ $recoveryCode }}</li>
                        @endforeach
                    </ul>
                @endif

                <x-slot:actions>
                    <x-ui.button wire:click="$toggle('showRecoveryCodes')">
                        {{ $showRecoveryCodes ? 'Hide recovery codes' : 'Show recovery codes' }}
                    </x-ui.button>
                    <x-ui.button wire:click="regenerateRecoveryCodes"
                        wire:confirm="Generate new codes? The old ones stop working.">Regenerate codes</x-ui.button>
                    <x-ui.button variant="danger" wire:click="disableTwoFactor"
                        wire:confirm="Turn off two-factor authentication?">Disable</x-ui.button>
                </x-slot:actions>
            @endif
        </x-form.form-section>

        {{-- Sesi browser --}}
        @if (config('session.driver') === 'database')
            <x-form.form-section title="Browser sessions"
                description="Devices that are logged in to your account right now." saved="sessions-saved"
                class="p-6 sm:p-8">
                <ul class="divide-y divide-slate-100">
                    @foreach ($this->sessions as $session)
                        <li class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                            <span
                                class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600">
                                <x-ui.icon :name="$session->mobile ? 'smartphone' : 'monitor'" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-900">{{ $session->browser }} on
                                    {{ $session->os }}</p>
                                <p class="text-xs text-slate-500">{{ $session->ip }}</p>
                            </div>
                            @if ($session->current)
                                <x-ui.badge tone="success">This device</x-ui.badge>
                            @else
                                <span class="text-xs text-slate-500">Active {{ $session->last }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <x-slot:actions>
                    <x-ui.button @click="$dispatch('modal-open-sessions')">Log out other sessions</x-ui.button>
                </x-slot:actions>
            </x-form.form-section>
        @endif
    </x-ui.card>

    <x-ui.modal name="sessions" title="Log out other sessions?"
        description="Enter your password to confirm. This device stays logged in.">
        <form wire:submit="logoutOtherSessions" class="space-y-5">
            <x-form.input label="Password" name="logoutPassword" type="password" wire:model="logoutPassword"
                autocomplete="current-password" required />

            <div class="flex justify-end gap-2">
                <x-ui.button @click="$dispatch('modal-close-sessions')">Cancel</x-ui.button>
                <x-ui.button type="submit" variant="dark" wire:loading.attr="disabled"
                    wire:target="logoutOtherSessions">Log out other sessions</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
