@props(['placeholder' => 'Search…', 'shortcut' => false, 'outline' => false])

@php
    $field = $outline ? 'rounded-xl border-slate-200 bg-white' : 'rounded-full border-transparent bg-slate-100/80';
@endphp

<div x-data="{ mac: /Mac|iPhone|iPad/.test(navigator.userAgent) }"
    @if ($shortcut) @keydown.window="if (($event.metaKey || $event.ctrlKey) && $event.key.toLowerCase() === 'k') { $event.preventDefault(); $refs.input.focus() }" @endif
    class="relative">
    <x-ui.icon name="search" size="sm" color="text-slate-400"
        class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2" />

    <input x-ref="input" type="search" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}"
        {{ $attributes->class("h-10 w-full border pl-10 text-sm text-slate-900 placeholder:text-slate-500 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-200 $field " . ($shortcut ? 'pr-16' : 'pr-4')) }}>

    @if ($shortcut)
        <kbd x-text="mac ? '⌘ K' : 'Ctrl K'"
            class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded-md bg-white px-1.5 py-0.5 text-[11px] font-medium text-slate-500 ring-1 ring-slate-200"></kbd>
    @endif
</div>
