@props([
    'src' => null,
    'name' => '',
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'size-8 text-xs',
        'md' => 'size-10 text-sm',
        'lg' => 'size-12 text-base',
        'xl' => 'size-20 text-2xl',
    ];
    $box = $sizes[$size] ?? $sizes['md'];
    $initials = Str::of($name)
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn($p) => Str::upper(Str::substr($p, 0, 1)))
        ->implode('');
@endphp

@if ($src)
    <img src="{{ $src }}" alt="" {{ $attributes->class("shrink-0 rounded-full object-cover $box") }}>
@else
    <span aria-hidden="true"
        {{ $attributes->class("grid shrink-0 place-items-center rounded-full bg-brand-100 font-semibold text-brand-700 $box") }}>{{ $initials }}</span>
@endif
