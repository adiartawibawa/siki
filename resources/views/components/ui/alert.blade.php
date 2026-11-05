@props(['tone' => 'info', 'title' => null, 'dismissible' => false])

@php
    $tones = [
        'success' => ['border-emerald-200 bg-emerald-50 text-emerald-900', 'circle-check', 'text-emerald-600'],
        'danger' => ['border-red-200 bg-red-50 text-red-900', 'circle-alert', 'text-red-600'],
        'warning' => ['border-amber-200 bg-amber-50 text-amber-900', 'triangle-alert', 'text-amber-600'],
        'info' => ['border-brand-200 bg-brand-50 text-brand-800', 'info', 'text-brand-600'],
    ];
    [$box, $icon, $iconColor] = $tones[$tone] ?? $tones['info'];
@endphp

<div x-data="{ open: true }" x-show="open" role="{{ in_array($tone, ['danger', 'warning']) ? 'alert' : 'status' }}"
    {{ $attributes->class("flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm $box") }}>
    <x-ui.icon :name="$icon" size="18" :color="$iconColor" class="mt-0.5" />

    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-medium">{{ $title }}</p>
        @endif
        <div @class(['leading-relaxed', 'mt-0.5' => $title])>{{ $slot }}</div>
    </div>

    @if ($dismissible)
        <button type="button" @click="open = false" aria-label="Dismiss"
            class="-mr-1 -mt-0.5 grid size-7 shrink-0 place-items-center rounded-lg opacity-70 transition hover:bg-black/5 hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
            <x-ui.icon name="x" size="sm" />
        </button>
    @endif
</div>
