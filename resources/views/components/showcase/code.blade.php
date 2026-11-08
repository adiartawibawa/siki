@props(['lang' => null])

@php
    $lines = preg_split('/\R/', (string) $slot);

    while ($lines && trim($lines[0]) === '') {
        array_shift($lines);
    }
    while ($lines && trim(end($lines)) === '') {
        array_pop($lines);
    }

    // Buang indentasi bersama agar kode rata kiri.
    $indent =
        collect($lines)->filter(fn($l) => trim($l) !== '')->map(fn($l) => strlen($l) - strlen(ltrim($l)))->min() ?? 0;

    $code = collect($lines)->map(fn($l) => substr($l, $indent))->implode("\n");
@endphp

<figure x-data="{ copied: false }" class="overflow-hidden rounded-2xl bg-slate-900">
    <figcaption class="flex items-center justify-between border-b border-white/10 px-4 py-2 text-xs text-slate-400">
        <span>{{ $lang }}</span>
        <button type="button"
            @click="navigator.clipboard.writeText($refs.src.textContent); copied = true; setTimeout(() => copied = false, 1500)"
            class="rounded-md px-2 py-1 transition hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
            <span x-text="copied ? 'Tersalin' : 'Salin'">Salin</span>
        </button>
    </figcaption>
    <pre class="overflow-x-auto p-4 text-xs leading-relaxed text-slate-100"><code x-ref="src">{!! e($code) !!}</code></pre>
</figure>
