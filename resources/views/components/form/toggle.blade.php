@props(['label', 'description' => null])

<label class="flex cursor-pointer items-start justify-between gap-4">
    <span>
        <span class="block text-sm font-medium text-slate-900">{{ $label }}</span>
        @if ($description)
            <span class="mt-0.5 block max-w-[48ch] text-xs leading-relaxed text-slate-500">{{ $description }}</span>
        @endif
    </span>

    <span class="relative mt-0.5 inline-flex shrink-0">
        <input type="checkbox" role="switch" {{ $attributes }} class="peer sr-only">
        <span
            class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-brand-600 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500 peer-focus-visible:ring-offset-2 motion-reduce:transition-none"></span>
        <span
            class="pointer-events-none absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:translate-x-5 motion-reduce:transition-none"></span>
    </span>
</label>
