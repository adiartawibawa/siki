<?php

use App\Models\Role;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Overview')] class extends Component
{
    /** Waktu di zona tampilan (Settings), bukan zona penyimpanan. */
    #[Computed]
    public function today(): Carbon
    {
        return Carbon::instance(
            now(app(GeneralSettings::class)->timezone)
        );
    }

    #[Computed]
    public function greeting(): string
    {
        $hour = $this->today->hour;

        return $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    }

    #[Computed]
    public function canSeeUsers(): bool
    {
        return Auth::user()->can('viewAny', User::class);
    }

    /** Satu query agregat; memakai titik sambung visibleTo() agar ikut pembatasan per aplikasi. */
    #[Computed]
    public function overview(): ?array
    {
        if (! $this->canSeeUsers) {
            return null;
        }

        $actor = Auth::user();

        $row = User::withTrashed()->visibleTo($actor)->selectRaw(
            'sum(case when deleted_at is null then 1 else 0 end) as active, '
            .'sum(case when deleted_at is null and email_verified_at is null then 1 else 0 end) as unverified, '
            .'sum(case when deleted_at is not null then 1 else 0 end) as deactivated'
        )->first();

        return [
            'active' => (int) $row->active,
            'unverified' => (int) $row->unverified,
            'deactivated' => (int) $row->deactivated,
            'roles' => $actor->can('viewAny', Role::class) ? Role::count() : null,
        ];
    }

    #[Computed]
    public function recent(): Collection
    {
        if (! $this->canSeeUsers) {
            return collect();
        }

        return User::query()->visibleTo(Auth::user())->with('roles')->latest()->limit(5)->get();
    }
};
?>

@php
    $user = auth()->user();
    $role = $user->primaryRole();
    $name = $user->first_name ?: $user->username;
    $verified = filled($user->email_verified_at);
    $twoFactor = filled($user->two_factor_confirmed_at);

    $overview = $this->overview;
    $cards = $overview ? array_values(array_filter([
        ['users', 'violet', $overview['active'], 'Registered and active', 'Active accounts'],
        ['mail', 'orange', $overview['unverified'], 'Waiting for email verification', 'Unverified'],
        ['user-x', 'pink', $overview['deactivated'], 'Cannot log in', 'Deactivated'],
        $overview['roles'] !== null ? ['shield', 'sky', $overview['roles'], 'Defined in this app', 'Roles'] : null,
    ])) : [];
@endphp

<div class="space-y-4">
    <x-ui.card>
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">{{ $this->greeting }}, {{ $name }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $this->today->translatedFormat('l, d F Y') }}</p>

        @if ($cards)
            <div class="{{ count($cards) === 4 ? 'xl:grid-cols-4' : 'xl:grid-cols-3' }} mt-5 grid gap-3 sm:grid-cols-2">
                @foreach ($cards as [$icon, $tone, $value, $caption, $label])
                    <x-ui.stat-card :icon="$icon" :tone="$tone" :value="number_format($value)" :caption="$caption" :label="$label" />
                @endforeach
            </div>
        @endif
    </x-ui.card>

    <div class="{{ $this->canSeeUsers ? 'xl:grid-cols-[minmax(0,1fr)_22rem]' : 'max-w-xl' }} grid gap-4">
        @if ($this->canSeeUsers)
            <x-ui.card>
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-semibold text-slate-900">Recent accounts</h2>
                    @if (Route::has('users.index'))
                        <x-ui.button variant="ghost" size="sm" :href="route('users.index')" navigate>View all</x-ui.button>
                    @endif
                </div>

                <ul class="mt-4 divide-y divide-slate-100">
                    @forelse ($this->recent as $u)
                        <li class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                            <x-ui.avatar :name="$u->name" size="sm" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900">{{ $u->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ '@' . $u->username }} · {{ $u->created_at?->diffForHumans() }}</p>
                            </div>
                            @if ($r = $u->primaryRole())
                                <x-ui.badge :tone="$r->tone()" :dot="false">{{ $r->name }}</x-ui.badge>
                            @endif
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-slate-500">No accounts yet.</li>
                    @endforelse
                </ul>
            </x-ui.card>
        @endif

        <x-ui.card>
            <h2 class="font-semibold text-slate-900">Your account</h2>

            <dl class="mt-4 divide-y divide-slate-100 text-sm">
                <div class="flex items-center justify-between gap-3 py-3">
                    <dt class="text-slate-500">Role</dt>
                    <dd>
                        @if ($role)
                            <x-ui.badge :tone="$role->tone()" :dot="false">{{ $role->name }}</x-ui.badge>
                        @else
                            <span class="text-slate-400">No role</span>
                        @endif
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-3 py-3">
                    <dt class="text-slate-500">Email</dt>
                    <dd class="flex min-w-0 items-center gap-2">
                        <span class="truncate font-medium text-slate-900">{{ $user->email }}</span>
                        <x-ui.badge :tone="$verified ? 'success' : 'warning'">{{ $verified ? 'Verified' : 'Unverified' }}</x-ui.badge>
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-3 py-3">
                    <dt class="text-slate-500">Two-factor</dt>
                    <dd><x-ui.badge :tone="$twoFactor ? 'success' : 'neutral'">{{ $twoFactor ? 'Enabled' : 'Not enabled' }}</x-ui.badge></dd>
                </div>
            </dl>

            <x-ui.button class="mt-4 w-full" :href="route('account.security')" navigate>Manage security</x-ui.button>
        </x-ui.card>
    </div>

    {{-- Titik sambung aplikasi turunan: buat resources/views/dashboard/extra.blade.php --}}
    @includeIf('dashboard.extra')
</div>
