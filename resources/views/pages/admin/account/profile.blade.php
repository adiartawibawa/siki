@php
    $user = auth()->user();
    $verified = $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail ? $user->hasVerifiedEmail() : null;
    $twoFactor = filled($user->two_factor_confirmed_at ?? null);

    $details = [
        ['Full name', $user->name],
        ['Email address', $user->email],
        ['Member since', $user->created_at?->translatedFormat('d F Y') ?? '-'],
    ];

    $security = [
        ['key', 'Password', null, null, 'Change'],
        [
            'shield-check',
            'Two-factor authentication',
            $twoFactor ? 'success' : 'neutral',
            $twoFactor ? 'Enabled' : 'Not enabled',
            'Manage',
        ],
    ];
    if ($verified !== null) {
        $security[] = [
            'mail',
            'Email verification',
            $verified ? 'success' : 'warning',
            $verified ? 'Verified' : 'Unverified',
            null,
        ];
    }
@endphp

<x-layouts::app title="Profile">
    <div class="space-y-4">

        <x-account.tabs />

        <x-ui.card :padded="false" class="overflow-hidden">
            <div class="h-28 bg-linear-to-tr from-brand-100 to-brand-500 sm:h-32"></div>

            <div class="px-5 pb-6 sm:px-8">
                <div class="-mt-10 flex flex-wrap items-end justify-between gap-4">
                    <x-ui.avatar :name="$user->name" size="xl" class="ring-4 ring-white" />
                    <x-ui.button variant="dark" icon="user" :href="route('account.details')">Edit profile</x-ui.button>
                </div>

                <h2 class="mt-4 text-2xl font-semibold tracking-tight text-slate-900">{{ $user->name }}</h2>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
            </div>
        </x-ui.card>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-ui.card>
                <h2 class="font-semibold text-slate-900">Details</h2>
                <dl class="mt-4 divide-y divide-slate-100 text-sm">
                    @foreach ($details as [$label, $value])
                        <div class="flex items-baseline justify-between gap-4 py-3">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="min-w-0 truncate font-medium text-slate-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h2 class="font-semibold text-slate-900">Account security</h2>
                <ul class="mt-4 divide-y divide-slate-100 text-sm">
                    @foreach ($security as [$icon, $label, $tone, $status, $link])
                        <li class="flex items-center gap-3 py-3">
                            <span
                                class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600">
                                <x-ui.icon :name="$icon" size="sm" />
                            </span>
                            <span class="min-w-0 flex-1 font-medium text-slate-900">{{ $label }}</span>
                            @if ($status)
                                <x-ui.badge :tone="$tone">{{ $status }}</x-ui.badge>
                            @endif
                            @if ($link)
                                <x-ui.button variant="ghost" size="sm"
                                    :href="route('account.security')">{{ $link }}</x-ui.button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>
    </div>
</x-layouts::app>
