@props(['value' => 0, 'max' => 100, 'label' => null, 'barClass' => 'bg-orange-500'])

@php $pct = $max > 0 ? min(100, max(0, $value / $max * 100)) : 0; @endphp

<div role="progressbar" aria-valuemin="0" aria-valuemax="{{ $max }}" aria-valuenow="{{ $value }}"
    @if ($label) aria-label="{{ $label }}" @endif
    {{ $attributes->class('h-1.5 w-full overflow-hidden rounded-full bg-slate-200') }}>
    <div class="h-full rounded-full {{ $barClass }}" style="width: {{ round($pct, 1) }}%"></div>
</div>
