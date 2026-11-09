<?php

use App\Actions\Api\CreateApiToken;
use App\Actions\Api\RevokeApiTokens;
use App\Enums\Ability;
use App\Enums\ApiScope;
use App\Livewire\Concerns\Notifies;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('API access')] class extends Component {
    use Notifies;

    public string $name = '';

    /** @var list<string> */
    public array $scopes = [];

    public string $days = '90';

    public string $password = '';

    /** Token mentah: tampil sekali, tidak pernah disimpan di database. */
    #[Locked]
    public ?string $plainToken = null;

    #[Computed]
    public function canCreate(): bool
    {
        return Auth::user()->can(Ability::ApiAccess->value);
    }

    /** @return list<ApiScope> */
    #[Computed]
    public function scopeOptions(): array
    {
        return ApiScope::availableTo(Auth::user());
    }

    #[Computed]
    public function tokens(): Collection
    {
        return Auth::user()->tokens()->latest()->get();
    }

    public function create(): void
    {
        $this->authorize(Ability::ApiAccess->value);

        $user = Auth::user();

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => [Rule::enum(ApiScope::class)],
            'days' => ['required', 'integer', Rule::in(CreateApiToken::LIFETIMES)],
            'password' => ['required', 'string'],
        ]);

        $this->confirmPassword($user);

        $this->plainToken = app(CreateApiToken::class)->handle($user, $data['name'], $data['scopes'], (int) $data['days']);

        $this->reset('name', 'scopes', 'password');
        $this->days = '90';
        unset($this->tokens);
    }

    public function revoke(int $id): void
    {
        app(RevokeApiTokens::class)->handle(Auth::user(), Auth::user(), $id);

        unset($this->tokens);
        $this->notify('Token revoked.');
    }

    public function dismissToken(): void
    {
        $this->plainToken = null;
    }

    private function confirmPassword($user): void
    {
        $key = 'api-token:' . $user->getAuthIdentifier();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'password' => 'Too many attempts. Try again in ' . RateLimiter::availableIn($key) . ' seconds.',
            ]);
        }

        if (!Hash::check($this->password, $user->password)) {
            RateLimiter::hit($key, 60);
            $this->reset('password');

            throw ValidationException::withMessages(['password' => 'The password is incorrect.']);
        }

        RateLimiter::clear($key);
    }
};
?>

<div class="space-y-4">
    <x-account.tabs />

    @if ($plainToken)
        <x-ui.alert tone="success" title="Your new token">
            <p>Copy it now. It won’t be shown again, and we don’t store it.</p>
            <div x-data="{ copied: false }" class="mt-3 flex flex-wrap items-center gap-2">
                <code x-ref="token"
                    class="min-w-0 flex-1 select-all break-all rounded-lg bg-white px-3 py-2 font-mono text-xs text-slate-900 ring-1 ring-emerald-200">{{ $plainToken }}</code>
                <x-ui.button size="sm"
                    @click="navigator.clipboard.writeText($refs.token.textContent.trim()); copied = true; setTimeout(() => copied = false, 1500)">
                    <span x-text="copied ? 'Copied' : 'Copy'">Copy</span>
                </x-ui.button>
                <x-ui.button size="sm" variant="dark" wire:click="dismissToken">I’ve copied it</x-ui.button>
            </div>
        </x-ui.alert>
    @endif

    <x-ui.card :padded="false" class="divide-y divide-slate-100 overflow-hidden">
        <x-form.form-section title="Create a token"
            description="A token lets a script or another system call the API as you, limited to the scopes you pick."
            class="p-6 sm:p-8">

            @if (!$this->canCreate)
                <x-ui.alert tone="warning">
                    API access is not enabled for your role. You can still revoke the tokens listed below.
                </x-ui.alert>
            @elseif ($this->scopeOptions === [])
                <x-ui.alert tone="info">
                    Your role doesn’t have any API scopes yet. Ask an administrator.
                </x-ui.alert>
            @else
                <form id="token-form" wire:submit="create" class="grid max-w-md gap-4">
                    <x-form.input label="Token name" name="name" wire:model="name"
                        placeholder="e.g. Integration server" autocomplete="off" required />

                    <fieldset>
                        <legend class="mb-1.5 text-xs font-medium text-slate-600">Scopes</legend>
                        <div class="space-y-3 rounded-xl border border-slate-200 p-3">
                            @foreach ($this->scopeOptions as $scope)
                                <label class="flex items-start gap-2.5 text-sm text-slate-800">
                                    <input type="checkbox" value="{{ $scope->value }}" wire:model="scopes"
                                        class="mt-0.5 size-4 rounded accent-brand-600">
                                    <span>
                                        {{ $scope->label() }}
                                        <span class="block text-xs text-slate-500">{{ $scope->description() }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('scopes')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <x-form.select label="Expires after" name="days" wire:model="days" :options="[30 => '30 days', 90 => '90 days', 180 => '180 days']" />

                    <x-form.input label="Confirm your password" name="password" type="password" wire:model="password"
                        autocomplete="current-password" required />
                </form>

                <x-slot:actions>
                    <x-ui.button type="submit" form="token-form" variant="dark" wire:loading.attr="disabled"
                        wire:target="create">Create token</x-ui.button>
                </x-slot:actions>
            @endif
        </x-form.form-section>

        <x-form.form-section title="Your tokens"
            description="Up to {{ \App\Actions\Api\CreateApiToken::MAX_TOKENS }} at a time. Revoke any you no longer use."
            class="p-6 sm:p-8">
            @if ($this->tokens->isEmpty())
                <p class="text-sm text-slate-500">You have no tokens.</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($this->tokens as $token)
                        <li wire:key="token-{{ $token->id }}"
                            class="flex flex-wrap items-center gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900">{{ $token->name }}</p>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    @foreach ($token->abilities as $ability)
                                        <x-ui.badge tone="neutral" :dot="false">{{ $ability }}</x-ui.badge>
                                    @endforeach
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    Last used {{ $token->last_used_at?->diffForHumans() ?? 'never' }}
                                    ·
                                    {{ $token->expires_at ? ($token->expires_at->isPast() ? 'expired' : 'expires ' . $token->expires_at->translatedFormat('d M Y')) : 'no expiry' }}
                                </p>
                            </div>
                            <x-ui.button size="sm" variant="ghost" class="text-red-600 hover:bg-red-50"
                                wire:click="revoke({{ $token->id }})"
                                wire:confirm="Revoke {{ $token->name }}? Anything using it will stop working."
                                aria-label="Revoke {{ $token->name }}">Revoke</x-ui.button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-form.form-section>

        <x-form.form-section title="Using a token" description="Send it as a bearer token." class="p-6 sm:p-8">
            <x-showcase.code lang="bash">
                @verbatim
                    curl -H "Authorization: Bearer YOUR_TOKEN" -H "Accept: application/json" \
                @endverbatim
                {{ url('/api/v1/users') }}
            </x-showcase.code>
        </x-form.form-section>
    </x-ui.card>
</div>
