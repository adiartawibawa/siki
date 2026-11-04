@props(['title', 'description' => null, 'saved' => null, 'danger' => false])

<section {{ $attributes->class('grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)] lg:gap-10') }}>
    <header>
        <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 max-w-[42ch] text-sm leading-relaxed text-slate-500">{{ $description }}</p>
        @endif
    </header>

    <div class="{{ $danger ? 'border-red-200' : 'border-slate-200' }} overflow-hidden rounded-2xl border bg-white">
        <div class="p-5">{{ $slot }}</div>

        @isset($actions)
            <div
                class="{{ $danger ? 'border-red-100 bg-red-50/60' : 'border-slate-100 bg-slate-50/70' }} flex flex-wrap items-center justify-end gap-3 border-t px-5 py-3.5">
                @if ($saved)
                    <span x-data="{ shown: false }"
                        x-on:{{ $saved }}.window="shown = true; setTimeout(() => shown = false, 2500)"
                        x-text="shown ? 'Saved.' : ''" role="status" aria-live="polite" class="text-sm text-slate-500"></span>
                @endif
                {{ $actions }}
            </div>
        @endisset
    </div>
</section>
