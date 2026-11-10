@props(['title' => 'Overview'])

@php
    $user = auth()->user();
    $name = $user?->name ?? 'Guest';
@endphp

<header
    class="sticky top-3 z-20 flex items-center gap-3 rounded-3xl bg-white px-4 py-3 ring-1 ring-slate-200/70 sm:px-5 lg:top-4">
    <button type="button" @click="drawer = true" aria-label="Open menu"
        class="grid size-10 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 lg:hidden">
        <x-ui.icon name="menu" />
    </button>

    <h1 class="text-lg font-semibold text-slate-900">{{ $title }}</h1>

    @if (config('admin.search'))
        <div class="mx-auto hidden w-full max-w-md md:block">
            <x-admin.search />
        </div>
    @else
        <div class="hidden flex-1 md:block"></div>
    @endif

    <div class="ml-auto flex items-center gap-2 md:ml-0">
        <x-ui.button variant="outline" size="icon" pill aria-label="Help">
            <x-ui.icon name="circle-question-mark" size="sm" />
        </x-ui.button>

        <x-admin.notifications />

        <x-ui.dropdown width="w-56"
            trigger-class="flex items-center gap-2.5 rounded-full py-1 pl-1 pr-2 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 sm:pr-3">
            <x-slot:trigger>
                <x-ui.avatar :name="$name" />
                <span class="hidden text-left leading-tight sm:block">
                    <span class="block text-sm font-medium text-slate-900">{{ $name }}</span>
                    <span class="block max-w-40 truncate text-xs text-slate-500">{{ $user?->email }}</span>
                </span>
                <x-ui.icon name="chevron-down" size="sm" color="text-slate-400" class="hidden sm:block" />
            </x-slot:trigger>

            <x-ui.dropdown-item href="{{ route('profile') }}" icon="user" wire:navigate>Profile</x-ui.dropdown-item>
            <x-ui.dropdown-item href="{{ route('account.details') }}" icon="sliders-horizontal" wire:navigate>Account
                settings</x-ui.dropdown-item>
            <x-ui.dropdown-item href="{{ route('account.security') }}" icon="shield-check"
                wire:navigate>Security</x-ui.dropdown-item>

            <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                @csrf
                <x-ui.dropdown-item as="button" type="submit" icon="log-out" danger>Log out</x-ui.dropdown-item>
            </form>
        </x-ui.dropdown>
    </div>
</header>
