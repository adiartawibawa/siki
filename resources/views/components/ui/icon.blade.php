{{--
    Component: <x-ui.icon />
    Merender file SVG lokal (resources/svg/lucide/{name}.svg) secara inline.

    Props:
    - name  : nama file SVG tanpa ekstensi (kebab-case), contoh: "check", "arrow-right"
    - size  : preset (xs|sm|md|lg|xl), angka px (24), atau nilai CSS (1.5rem). Default: md
    - color : kelas Tailwind "text-*" atau nilai CSS (#ef4444, rgb(), var(--x)). Default: warisi warna teks
    - class : kelas tambahan (otomatis digabung lewat $attributes)

    Pemakaian:
    Default (size md, warna mengikuti teks)
    <x-ui.icon name="check" />

    Preset size + kelas Tailwind
    <x-ui.icon name="trash-2" size="lg" color="text-red-500" />

    Angka (px) + warna hex
    <x-ui.icon name="user" size="24" color="#3b82f6" />

    Nilai CSS + kelas tambahan
    <x-ui.icon name="loader-circle" size="1.5rem" class="animate-spin" />

    Atribut kosong tetap aman
    <x-ui.icon name="check" size="" color="" class="" />

    Atribut SVG lain tetap bisa dilewatkan
    <x-ui.icon name="check" stroke-width="1.5" />
--}}

@props(['name', 'size' => 'md', 'color' => null])

@php
    // ------------------------------------------------------------------
    // 1. Baca file SVG
    // ------------------------------------------------------------------
    // Validasi nama: hanya huruf kecil, angka, dan tanda hubung.
    // Ini mencegah path traversal seperti name="../../.env".
    $svg = null;

    if (preg_match('/^[a-z0-9-]+$/', (string) $name) === 1) {
        $path = resource_path("svg/lucide/{$name}.svg");

        if (is_file($path)) {
            $svg = file_get_contents($path);
        }
    }

    // ------------------------------------------------------------------
    // 2. Tentukan ukuran
    // ------------------------------------------------------------------
    // Preset ditulis literal agar terdeteksi oleh Tailwind JIT.
    $presets = [
        'xs' => 'size-3',
        'sm' => 'size-4',
        'md' => 'size-5',
        'lg' => 'size-6',
        'xl' => 'size-8',
    ];

    $classes = ['shrink-0']; // Cegah ikon mengecil di dalam flex container
    $styles = [];

    // Nilai kosong ("") dianggap default "md"
    $size = filled($size) ? trim((string) $size) : 'md';

    if (isset($presets[$size])) {
        // Preset -> kelas Tailwind
        $classes[] = $presets[$size];
    } elseif (is_numeric($size)) {
        // Angka murni -> piksel
        $styles[] = "width: {$size}px; height: {$size}px";
    } elseif (preg_match('/^\d+(\.\d+)?(px|rem|em|%)$/', $size)) {
        // Nilai CSS valid, contoh: 1.5rem
        $styles[] = "width: {$size}; height: {$size}";
    } else {
        // Format tidak valid -> fallback ke md
        $classes[] = $presets['md'];
    }

    // ------------------------------------------------------------------
    // 3. Tentukan warna
    // ------------------------------------------------------------------
    if (filled($color)) {
        if (str_starts_with($color, 'text-')) {
            // Kelas Tailwind, contoh: text-red-500
            $classes[] = $color;
        } else {
            // Nilai CSS langsung, contoh: #3b82f6
            $styles[] = "color: {$color}";
        }
    }

    // ------------------------------------------------------------------
    // 4. Atribut tambahan
    // ------------------------------------------------------------------
    $extra = ['aria-hidden' => 'true']; // Ikon dekoratif, disembunyikan dari screen reader

    if ($styles) {
        $extra['style'] = implode('; ', $styles);
    }

    // ------------------------------------------------------------------
    // 5. Modifikasi tag <svg>
    // ------------------------------------------------------------------
    if ($svg) {
        // Gabungkan class, style, dan atribut lain dari pemanggil
        $attrs = (string) $attributes->class($classes)->merge($extra);

        $svg = preg_replace_callback(
            '/<svg\b[^>]*>/',
            function (array $match) use ($attrs) {
                // Hapus width/height/class bawaan Lucide agar tidak bentrok
                $tag = preg_replace('/\s(?:width|height|class)="[^"]*"/', '', $match[0]);

                // Sisipkan atribut kita tepat setelah "<svg"
                return preg_replace('/^<svg\b/', '<svg ' . $attrs, $tag);
            },
            $svg,
            1, // Hanya tag <svg> pertama
        );
    }
@endphp

{{-- Tampilkan SVG. Jika file tidak ditemukan, render komentar HTML agar mudah di-debug. --}}
@if ($svg)
    {!! $svg !!}
@else
    <!-- icon "{{ $name }}" tidak ditemukan di resources/svg/lucide -->
@endif
