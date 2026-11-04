@props(['last4', 'exp', 'status' => 'Active'])

<div
    {{ $attributes->class('relative aspect-[1.7/1] overflow-hidden rounded-2xl bg-linear-to-br from-brand-500 to-brand-800 p-5 text-white shadow-lg shadow-brand-700/20') }}>
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-2">
            <x-ui.icon name="wifi" size="sm" class="rotate-90 text-white/80" />
            <span class="rounded-md bg-white/20 px-2 py-0.5 text-[11px] font-medium">{{ $status }}</span>
        </div>
        <span class="flex" aria-hidden="true">
            <span class="size-6 rounded-full bg-white/80"></span>
            <span class="-ml-2.5 size-6 rounded-full bg-white/40"></span>
        </span>
    </div>

    <div class="absolute inset-x-5 bottom-5 flex items-end justify-between">
        <div>
            <p class="text-[11px] text-white/70">Card number</p>
            <p class="text-sm font-medium tracking-wider">•••• •••• {{ $last4 }}</p>
        </div>
        <div class="text-right">
            <p class="text-[11px] text-white/70">Expires</p>
            <p class="text-sm font-medium">{{ $exp }}</p>
        </div>
    </div>
</div>
