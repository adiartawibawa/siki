@props(['icon', 'tone' => 'violet', 'value', 'caption' => null, 'label'])

@php
    $tones = [
        'violet' => 'from-violet-400 to-violet-600',
        'orange' => 'from-orange-400 to-red-500',
        'pink' => 'from-pink-400 to-rose-500',
        'sky' => 'from-sky-400 to-blue-500',
    ];
@endphp

<div {{ $attributes->class('rounded-3xl bg-slate-50 p-1.5 ring-1 ring-slate-200/60') }}>
    <div class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-[0_1px_2px_rgba(15,23,42,.04)]">
        <span
            class="grid size-12 shrink-0 place-items-center rounded-xl bg-linear-to-br text-white {{ $tones[$tone] ?? $tones['violet'] }}">
            <x-ui.icon :name="$icon" />
        </span>
        <div class="min-w-0">
            <p class="truncate text-xl font-semibold tracking-tight text-slate-900">{{ $value }}</p>
            @if ($caption)
                <p class="truncate text-xs text-slate-500">{{ $caption }}</p>
            @endif
        </div>
    </div>

    <div class="flex items-center justify-between px-3 pb-1.5 pt-2.5">
        <p class="text-sm font-medium text-slate-800">{{ $label }}</p>

        @isset($actions)
            <x-ui.dropdown width="w-44"
                trigger-class="grid size-7 place-items-center rounded-lg text-slate-500 hover:bg-slate-200/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                <x-slot:trigger>
                    <x-ui.icon name="ellipsis" size="sm" />
                    <span class="sr-only">Options for {{ $label }}</span>
                </x-slot:trigger>
                {{ $actions }}
            </x-ui.dropdown>
        @endisset
    </div>
</div>
