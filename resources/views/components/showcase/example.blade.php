@props(['title' => null])

@php
    $source = (string) $slot;
    $html = null;
    $error = null;

    try {
        $html = \Illuminate\Support\Facades\Blade::render($source, [], true);
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
@endphp

<div class="space-y-3">
    @if ($title)
        <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
    @endif

    <div {{ $attributes->class('rounded-2xl border border-dashed border-slate-300 bg-slate-50/60 p-5') }}>
        @if ($error)
            <x-ui.alert tone="danger" title="Komponen ini gagal dirender">{{ $error }}</x-ui.alert>
        @else
            {!! $html !!}
        @endif
    </div>

    <x-showcase.code lang="blade">{!! $source !!}</x-showcase.code>
</div>
