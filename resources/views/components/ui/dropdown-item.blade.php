@props(['icon' => null, 'as' => 'a', 'danger' => false])

@php
    $classes =
        'flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 ' .
        ($danger ? 'text-red-600 hover:bg-red-50' : 'text-slate-700 hover:bg-slate-50');
@endphp

@if ($as === 'button')
    <button {{ $attributes->class($classes)->merge(['type' => 'button', 'role' => 'menuitem']) }}>
        @if ($icon)
            <x-ui.icon :name="$icon" size="sm" />
        @endif
        {{ $slot }}
    </button>
@else
    <a {{ $attributes->class($classes)->merge(['href' => '#', 'role' => 'menuitem']) }}>
        @if ($icon)
            <x-ui.icon :name="$icon" size="sm" />
        @endif
        {{ $slot }}
    </a>
@endif
