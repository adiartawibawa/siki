@props([
    'variant' => 'outline',
    'size' => 'md',
    'icon' => null,
    'iconRight' => null,
    'href' => null,
    'pill' => false,
    'navigate' => false,
])

@php
    $variants = [
        'dark' => 'bg-slate-900 text-white hover:bg-slate-800',
        'primary' => 'bg-brand-600 text-white hover:bg-brand-700',
        'outline' => 'border border-slate-200 bg-white text-slate-800 hover:bg-slate-50',
        'ghost' => 'text-slate-600 hover:bg-slate-100',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
    ];
    $sizes = [
        'sm' => 'h-8 gap-1.5 px-3 text-xs',
        'md' => 'h-10 gap-2 px-4 text-sm',
        'icon' => 'size-10',
        'icon-sm' => 'size-8',
    ];
    $tag = $href ? 'a' : 'button';
    $extra = $href ? ['href' => $href] + ($navigate ? ['wire:navigate' => true] : []) : ['type' => 'button'];
    $classes = [
        'inline-flex shrink-0 items-center justify-center font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        $variants[$variant] ?? $variants['outline'],
        $sizes[$size] ?? $sizes['md'],
        $pill ? 'rounded-full' : 'rounded-xl',
    ];
@endphp

<{{ $tag }} {{ $attributes->merge($extra)->class($classes) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" size="sm" />
    @endif
    {{ $slot }}
    @if ($iconRight)
        <x-ui.icon :name="$iconRight" size="sm" />
    @endif
    </{{ $tag }}>
