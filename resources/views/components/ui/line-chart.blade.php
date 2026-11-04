@props([
    'labels', // ['Jan', 'Feb', ...]
    'series', // [['name' => 'Earning', 'tone' => 'brand|teal|mint', 'data' => [...]], ...]
    'highlight' => 0, // indeks label yang tooltip-nya tampil awal
    'prefix' => '$',
    'height' => 260,
])

@php
    $tones = [
        'brand' => ['stroke' => 'stroke-brand-700', 'dot' => 'bg-brand-700'],
        'teal' => ['stroke' => 'stroke-teal-400', 'dot' => 'bg-teal-400'],
        'mint' => ['stroke' => 'stroke-teal-200', 'dot' => 'bg-teal-200'],
    ];

    $W = 720;
    $H = (int) $height;
    $pl = 44;
    $pr = 28;
    $pt = 44;
    $pb = 30;
    $iw = $W - $pl - $pr;
    $ih = $H - $pt - $pb;
    $n = count($labels);

    // Skala sumbu Y yang "bulat" (10k, 20k, ...)
    $maxVal = max(1, collect($series)->flatMap(fn($s) => $s['data'])->max());
    $raw = $maxVal / 4;
    $pow = 10 ** floor(log10($raw));
    $norm = $raw / $pow;
    $step = ($norm <= 1 ? 1 : ($norm <= 2 ? 2 : ($norm <= 5 ? 5 : 10))) * $pow;
    $top = $step * ceil($maxVal / $step);
    $ticks = range(0, $top, $step);

    $x = fn(int $i) => $pl + ($n > 1 ? ($iw * $i) / ($n - 1) : $iw / 2);
    $y = fn($v) => $pt + $ih * (1 - $v / $top);
    $fmt = fn($v) => $v >= 1000 ? rtrim(rtrim(number_format($v / 1000, 1), '0'), '.') . 'k' : (string) $v;

    // Kurva mulus (Catmull-Rom -> Bezier)
    $curve = function (array $data) use ($x, $y) {
        $pts = [];
        foreach (array_values($data) as $i => $v) {
            $pts[] = [$x($i), $y($v)];
        }
        $c = count($pts);
        $d = sprintf('M%.1f %.1f', $pts[0][0], $pts[0][1]);
        for ($i = 0; $i < $c - 1; $i++) {
            $p0 = $pts[max($i - 1, 0)];
            $p1 = $pts[$i];
            $p2 = $pts[$i + 1];
            $p3 = $pts[min($i + 2, $c - 1)];
            $d .= sprintf(
                ' C%.1f %.1f %.1f %.1f %.1f %.1f',
                $p1[0] + ($p2[0] - $p0[0]) / 6,
                $p1[1] + ($p2[1] - $p0[1]) / 6,
                $p2[0] - ($p3[0] - $p1[0]) / 6,
                $p2[1] - ($p3[1] - $p1[1]) / 6,
                $p2[0],
                $p2[1],
            );
        }
        return $d;
    };

    // Titik untuk tooltip: mengikuti seri pertama
    $points = collect($series[0]['data'])
        ->values()
        ->map(
            fn($v, $i) => [
                'x' => round(($x($i) / $W) * 100, 2),
                'y' => round(($y($v) / $H) * 100, 2),
                'text' => $prefix . number_format($v),
            ],
        )
        ->all();

    $colW = $n > 1 ? $iw / ($n - 1) : $iw;
    $summary = 'Line chart of ' . collect($series)->pluck('name')->join(', ') . ' by ' . $n . ' periods.';
@endphp

<div x-data="{ i: {{ (int) $highlight }}, home: {{ (int) $highlight }}, pts: @js($points) }" {{ $attributes }}>
    <ul class="mb-2 flex flex-wrap items-center justify-end gap-x-4 gap-y-1 text-xs text-slate-600">
        @foreach ($series as $s)
            <li class="flex items-center gap-1.5">
                <span class="size-2 rounded-full {{ $tones[$s['tone'] ?? 'brand']['dot'] }}" aria-hidden="true"></span>
                {{ $s['name'] }}
            </li>
        @endforeach
    </ul>

    <div class="overflow-x-auto" @mouseleave="i = home">
        <div class="relative min-w-[34rem]">
            <svg viewBox="0 0 {{ $W }} {{ $H }}" class="block h-auto w-full" role="img"
                aria-label="{{ $summary }}">
                @foreach ($ticks as $t)
                    <line x1="{{ $pl }}" x2="{{ $W - $pr }}" y1="{{ $y($t) }}"
                        y2="{{ $y($t) }}" class="stroke-slate-200" stroke-dasharray="3 5" />
                    <text x="{{ $pl - 10 }}" y="{{ $y($t) + 4 }}" text-anchor="end"
                        class="fill-slate-500 text-[11px]">{{ $fmt($t) }}</text>
                @endforeach

                @foreach ($labels as $i => $label)
                    <text x="{{ $x($i) }}" y="{{ $H - 8 }}" text-anchor="middle"
                        class="fill-slate-500 text-[11px]">{{ $label }}</text>
                @endforeach

                {{-- Seri pertama digambar paling akhir agar berada di atas --}}
                @foreach (array_reverse($series, true) as $s)
                    <path d="{{ $curve($s['data']) }}" fill="none" stroke-width="2.5" stroke-linecap="round"
                        class="{{ $tones[$s['tone'] ?? 'brand']['stroke'] }}" />
                @endforeach

                @foreach ($labels as $i => $label)
                    <rect x="{{ $x($i) - $colW / 2 }}" y="0" width="{{ $colW }}" height="{{ $H - $pb }}"
                        fill="transparent" @mouseenter="i = {{ $i }}" />
                @endforeach
            </svg>

            <div class="pointer-events-none absolute border-l border-dashed border-brand-500/60"
                :style="`left:${pts[i].x}%; top:{{ round(($pt / $H) * 100, 2) }}%; bottom:{{ round(($pb / $H) * 100, 2) }}%`">
            </div>
            <div class="pointer-events-none absolute" :style="`left:${pts[i].x}%; top:${pts[i].y}%`">
                <span
                    class="absolute left-0 top-0 size-3 -translate-x-1/2 -translate-y-1/2 rounded-full bg-brand-700 ring-2 ring-white"></span>
                <span x-text="pts[i].text"
                    class="absolute bottom-3 left-0 -translate-x-1/2 whitespace-nowrap rounded-lg bg-brand-700 px-2.5 py-1 text-xs font-semibold text-white"></span>
            </div>
        </div>
    </div>
</div>
