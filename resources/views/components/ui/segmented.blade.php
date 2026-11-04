@props(['options', 'value' => null])

@php $value ??= array_key_first($options); @endphp

<div x-data="{ value: '{{ $value }}' }" x-modelable="value" role="radiogroup"
    {{ $attributes->class('inline-flex gap-1 rounded-full border border-slate-200 bg-white p-1') }}>
    @foreach ($options as $key => $label)
        <button type="button" role="radio" @click="value = '{{ $key }}'"
            :aria-checked="value === '{{ $key }}'"
            :class="value === '{{ $key }}' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900'"
            class="inline-flex h-8 items-center gap-1.5 rounded-full px-3.5 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
            <span x-show="value === '{{ $key }}'" class="size-1.5 rounded-full bg-white"
                aria-hidden="true"></span>
            {{ $label }}
        </button>
    @endforeach
</div>
