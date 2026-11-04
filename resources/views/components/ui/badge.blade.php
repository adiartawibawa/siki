@props(['tone' => 'neutral', 'dot' => true])

@php
    $tones = [
        'success' => ['bg-emerald-50 text-emerald-700', 'bg-emerald-500'],
        'danger' => ['bg-red-50 text-red-700', 'bg-red-500'],
        'warning' => ['bg-amber-50 text-amber-700', 'bg-amber-500'],
        'brand' => ['bg-brand-50 text-brand-700', 'bg-brand-600'],
        'neutral' => ['bg-slate-100 text-slate-700', 'bg-slate-400'],
    ];
    [$box, $dotColor] = $tones[$tone] ?? $tones['neutral'];
@endphp

<span {{ $attributes->class("inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium $box") }}>
    @if ($dot)
        <span class="size-1.5 rounded-full {{ $dotColor }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
