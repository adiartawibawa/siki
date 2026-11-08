@props(['id', 'title', 'intro' => null])

<section id="{{ $id }}" class="scroll-mt-24">
    <x-ui.card class="space-y-6">
        <header>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">{{ $title }}</h2>
            @if ($intro)
                <p class="mt-1.5 max-w-[70ch] text-sm leading-relaxed text-slate-600">{{ $intro }}</p>
            @endif
        </header>

        {{ $slot }}
    </x-ui.card>
</section>
