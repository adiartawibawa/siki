@props(['padded' => true])

<section {{ $attributes->class(['rounded-3xl bg-white ring-1 ring-slate-200/70', 'p-5' => $padded]) }}>
    {{ $slot }}
</section>
