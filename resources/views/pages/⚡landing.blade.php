<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman landing publik untuk boilerplate TALL Stack (Single File Component).
 * Memakai layout 'layouts.guest' (yang meng-extend 'layouts.base').
 */
new #[Layout('layouts.guest')] #[Title('Boilerplate TALL Stack')] class extends Component {
    /**
     * Dijalankan sebelum halaman dirender.
     * Bila landing dinonaktifkan lewat config('starter.landing'), pengunjung
     * dialihkan ke halaman masuk (atau beranda aplikasi jika sudah login).
     */
    public function mount()
    {
        if (!config('starter.landing')) {
            // Ganti 'dashboard' dengan nama route beranda aplikasi setelah login.
            return $this->redirectRoute(auth()->check() ? 'dashboard' : 'login');
        }
    }

    /**
     * Daftar pertanyaan & jawaban untuk bagian FAQ.
     * Computed property: tidak ikut dikirim ke browser sebagai state komponen.
     * Diakses di markup lewat $this->faqs.
     *
     * @return array<int, array{q: string, a: string}>
     */
    #[Computed]
    public function faqs(): array
    {
        return [
            [
                'q' => 'Teknologi apa saja yang dipakai?',
                'a' => 'Laravel 13 sebagai backend, Livewire 4 untuk komponen dinamis, Alpine.js untuk interaksi ringan di browser, dan Tailwind CSS 4 untuk tampilan.',
            ],
            [
                'q' => 'Apa saja yang sudah terpasang?',
                'a' => 'Autentikasi, otorisasi, setup aplikasi, integrasi API, dan dokumentasi aplikasi yang tersusun otomatis.',
            ],
            [
                'q' => 'Apakah dokumentasi perlu ditulis manual?',
                'a' => 'Tidak. Dokumentasi dibuat otomatis dari kode aplikasi, sehingga ikut berubah saat Anda menambah atau mengubah fitur.',
            ],
            [
                'q' => 'Bagaimana cara memulainya?',
                'a' => 'Clone proyek, pasang dependensi dengan Composer dan npm, jalankan migrasi, lalu jalankan server pengembangan. Langkah lengkapnya ada di dokumentasi.',
            ],
        ];
    }
};
?>

{{-- Livewire mewajibkan satu elemen root --}}
<div>
    @php
        // URL tujuan tombol. Jatuh ke '#' bila rute belum didefinisikan.
        $loginUrl = Route::has('login') ? route('login') : '#';
        $registerUrl = Route::has('register') ? route('register') : $loginUrl;

        // Empat lapisan kaca pada hero (urut dari bawah ke atas).
        // 'y' = jarak vertikal antar pelat, 'delay' = jeda animasi, 'tint' = warna gradasi kaca.
        $plates = [
            ['y' => 150, 'delay' => 0, 'tint' => 'from-cyan-300/60 to-brand-500/50'],
            ['y' => 100, 'delay' => 120, 'tint' => 'from-cyan-200/50 to-brand-200/40'],
            ['y' => 50, 'delay' => 240, 'tint' => 'from-white/40 to-cyan-100/30'],
            ['y' => 0, 'delay' => 360, 'tint' => 'from-white/70 to-brand-100/50'],
        ];

        // Matriks izin untuk contoh tampilan otorisasi: [peran, lihat, ubah, hapus].
        $roles = [['Admin', true, true, true], ['Operator', true, true, false], ['Regular User', true, false, false]];

        // Kelas bersama untuk tombol di hero dan CTA.
        $btn =
            'inline-flex items-center gap-2 rounded-full px-6 py-3 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600';
    @endphp

    {{-- ============================================================
         HERO
         ============================================================ --}}
    <section class="relative isolate min-h-screen overflow-hidden bg-white">

        {{-- Lengkung hitam di kiri atas (latar logo) --}}
        <svg aria-hidden="true" viewBox="0 0 300 200" class="absolute left-0 top-0 -z-10 h-32 w-56 lg:h-52 lg:w-72"
            preserveAspectRatio="none">
            <path d="M0 0H282C280 62 238 92 178 114C104 140 58 152 32 200H0Z" fill="#000" />
        </svg>

        {{-- Header: logo, navigasi, tombol masuk --}}
        <header x-data="{ menu: false }" class="relative z-30 flex items-center justify-between px-6 py-6 lg:px-12">
            {{-- Logo: tanda lapisan bertumpuk + nama aplikasi --}}
            <a href="{{ url('/') }}"
                class="flex items-center gap-2 text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                <svg aria-hidden="true" viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linejoin="round">
                    <path d="M12 3l9 4.5-9 4.5-9-4.5z" />
                    <path d="M3 12l9 4.5 9-4.5" />
                    <path d="M3 16.5L12 21l9-4.5" />
                </svg>
                <span class="font-display text-xl font-medium tracking-wide">{{ config('app.name') }}</span>
            </a>

            {{-- Navigasi desktop --}}
            <nav aria-label="Navigasi utama" class="hidden items-center gap-10 text-sm text-black/80 lg:flex">
                <a href="#fitur"
                    class="hover:text-black focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600">Fitur</a>
                <a href="#faq"
                    class="hover:text-black focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600">FAQ</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ $loginUrl }}"
                    class="{{ $btn }} max-lg:hidden border border-black/40 text-black hover:bg-black hover:text-white">Masuk</a>

                {{-- Tombol menu mobile (Alpine) --}}
                <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-controls="menu-mobile"
                    aria-label="Buka menu"
                    class="grid size-11 place-items-center rounded-full border border-black/20 bg-white text-black lg:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round">
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
            </div>

            {{-- Panel menu mobile --}}
            <div id="menu-mobile" x-show="menu" x-cloak @click.outside="menu = false"
                class="absolute inset-x-6 top-20 rounded-3xl bg-white p-6 text-black shadow-xl ring-1 ring-black/10 lg:hidden">
                <nav aria-label="Navigasi mobile" class="flex flex-col gap-4 text-base">
                    <a href="#fitur" @click="menu = false">Fitur</a>
                    <a href="#faq" @click="menu = false">FAQ</a>
                    <a href="{{ $loginUrl }}" class="font-medium">Masuk</a>
                </nav>
            </div>
        </header>

        {{-- Konten teks hero --}}
        <div
            class="relative z-20 flex flex-col px-6 pb-10 pt-10 lg:min-h-[calc(100svh-6rem)] lg:pl-[26.5rem] lg:pr-12 lg:pt-4">
            <h1
                class="font-display text-[2.75rem] font-medium leading-[0.95] tracking-tight text-black sm:text-7xl xl:text-[5.75rem]">
                Bangun aplikasi<br class="hidden sm:inline">
                Laravel tanpa<br class="hidden sm:inline">
                mulai dari nol.
            </h1>

            {{-- Penjelasan singkat --}}
            <div class="mt-10 flex items-start gap-4 lg:ml-36">
                <span aria-hidden="true" class="mt-2.5 h-px w-16 shrink-0 bg-black"></span>
                <p class="max-w-sm text-sm leading-relaxed text-black/75">
                    Boilerplate TALL Stack dengan autentikasi, otorisasi, setup aplikasi, integrasi API, dan dokumentasi
                    otomatis yang sudah terpasang.
                </p>
            </div>

            {{-- Tombol aksi utama --}}
            <div class="mt-8 flex flex-wrap gap-3 lg:ml-36">
                <a href="{{ $registerUrl }}" class="{{ $btn }} bg-black text-white hover:bg-brand-700">
                    Mulai sekarang
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 17L17 7M8 7h9v9" />
                    </svg>
                </a>
            </div>

            {{-- Baris bawah: fakta stack, penunjuk gulir, dan dua lingkaran ikon --}}
            <div class="mt-16 flex flex-wrap items-end gap-x-14 gap-y-8 lg:mt-auto lg:pt-16">
                <div>
                    <p class="font-display text-4xl font-medium tracking-tight sm:text-5xl">Laravel 13</p>
                    <p class="mt-2 max-w-48 text-xs leading-snug text-black/70">Livewire 4, Alpine.js, dan Tailwind CSS
                        4 sudah terpasang.</p>
                </div>

                <a href="#fitur"
                    class="flex items-start gap-3 text-xs leading-snug text-black/80 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600">
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="mt-0.5 size-4 shrink-0" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <rect x="6" y="3" width="12" height="18" rx="6" />
                        <path d="M12 7v4" />
                    </svg>
                    Gulir untuk<br>melihat fitur
                </a>

                {{-- Dua lingkaran: integrasi API dan dokumentasi --}}
                <div aria-hidden="true" class="ml-auto hidden items-center sm:flex">
                    <span class="grid size-24 place-items-center rounded-full bg-canvas text-black ring-1 ring-black/5">
                        <svg viewBox="0 0 24 24" class="size-10" fill="none" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 9l-4 3 4 3M16 9l4 3-4 3M13.5 6l-3 12" />
                        </svg>
                    </span>
                    <span class="-ml-5 grid size-24 place-items-center rounded-full bg-brand-600 text-white">
                        <svg viewBox="0 0 24 24" class="size-10" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 4h10a4 4 0 014 4v12H9a4 4 0 01-4-4z" />
                            <path d="M9 9h6M9 13h6" />
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        {{-- Panel lengkung biru berisi pelat kaca (visual utama).
             Mobile: tampil di bawah teks. Desktop: menempel di kiri, menimpa bagian bawah. --}}
        <div aria-hidden="true"
            class="relative mx-auto mb-0 h-[36rem] w-[min(22rem,calc(100%-3rem))] rounded-t-full bg-brand-600 ring-[12px] ring-white
                   lg:absolute lg:bottom-0 lg:left-6 lg:top-28 lg:z-10 lg:mx-0 lg:h-auto lg:w-[24rem] lg:ring-[14px]">

            {{-- Pelat kaca: dirotasi menjadi bentuk belah ketupat isometrik --}}
            <div class="absolute left-1/2 top-[25%] lg:top-[38%] -translate-x-1/2 -translate-y-1/2">
                @foreach ($plates as $plate)
                    <div style="--y: {{ $plate['y'] }}px; animation-delay: {{ $plate['delay'] }}ms"
                        class="animate-plate-in motion-reduce:animate-none absolute -left-28 -top-28 size-56 rounded-4xl lg:-left-36 lg:-top-36 lg:size-72 border border-white/80 bg-linear-to-br {{ $plate['tint'] }}
                               shadow-[inset_0_0_32px_rgba(255,255,255,0.35),0_10px_0_-2px_rgba(255,255,255,0.25)] backdrop-blur-[2px]
                               [transform:translateY(var(--y))_rotateX(62deg)_rotateZ(45deg)]">
                    </div>
                @endforeach
            </div>

            {{-- Kartu legenda: menamai keempat lapisan --}}
            <div class="absolute bottom-6 left-6 w-44 rounded-3xl bg-white p-4 text-black shadow-lg">
                <svg viewBox="0 0 24 24" class="size-8 text-brand-600" fill="none" stroke="currentColor"
                    stroke-width="1.8" stroke-linejoin="round">
                    <path d="M12 3l9 4.5-9 4.5-9-4.5z" />
                    <path d="M3 12l9 4.5 9-4.5" />
                    <path d="M3 16.5L12 21l9-4.5" />
                </svg>
                <p class="mt-3 font-display text-lg font-medium leading-tight">TALL Stack</p>
                <ul class="mt-1 text-xs leading-relaxed text-black/70">
                    <li>Tailwind CSS 4</li>
                    <li>Alpine.js</li>
                    <li>Laravel 13</li>
                    <li>Livewire 4</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- ============================================================
         FITUR: lima bagian yang sudah terpasang
         ============================================================ --}}
    <section id="fitur" class="bg-canvas px-6 py-24 lg:px-12">
        <div class="mx-auto max-w-6xl">
            <h2 class="max-w-3xl font-display text-4xl font-medium leading-[1.05] tracking-tight sm:text-5xl">
                Lima bagian yang selalu dibangun ulang, sudah terpasang.
            </h2>

            <div class="mt-14 grid gap-4 lg:grid-cols-6">

                {{-- Autentikasi: kartu hitam dengan contoh form masuk --}}
                <article
                    class="flex flex-col justify-between gap-8 rounded-[2rem] bg-black p-8 text-white lg:col-span-3">
                    <div>
                        <h3 class="font-display text-2xl font-medium">Autentikasi</h3>
                        <p class="mt-2 max-w-sm text-sm leading-relaxed text-white/70">Alur masuk dan daftar sudah
                            siap. Anda langsung mengerjakan fitur, bukan halaman login.</p>
                    </div>
                    <div aria-hidden="true" class="w-full max-w-xs space-y-3 rounded-2xl bg-white/10 p-5 sm:self-end">
                        <div class="h-9 rounded-lg bg-white/15 px-3 text-xs leading-9 text-white/60">
                            nama@perusahaan.com</div>
                        <div class="h-9 rounded-lg bg-white/15 px-3 text-xs leading-9 text-white/60">••••••••</div>
                        <div class="h-9 rounded-full bg-white text-center text-xs font-medium leading-9 text-black">
                            Masuk</div>
                    </div>
                </article>

                {{-- Otorisasi: kartu putih dengan matriks peran dan izin --}}
                <article class="flex flex-col justify-between gap-8 rounded-[2rem] bg-white p-8 lg:col-span-3">
                    <div>
                        <h3 class="font-display text-2xl font-medium">Otorisasi</h3>
                        <p class="mt-2 max-w-sm text-sm leading-relaxed text-black/70">Atur siapa yang boleh melihat,
                            mengubah, dan menghapus.</p>
                    </div>
                    <table aria-hidden="true" class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-xs text-black/55">
                                <th class="pb-2 font-normal">Peran</th>
                                <th class="pb-2 font-normal">Lihat</th>
                                <th class="pb-2 font-normal">Ubah</th>
                                <th class="pb-2 font-normal">Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as [$role, $view, $edit, $delete])
                                <tr class="border-t border-black/10">
                                    <td class="py-2.5 font-medium">{{ $role }}</td>
                                    <td class="{{ $view ? 'text-brand-600' : 'text-black/25' }}">
                                        {{ $view ? '✓' : '–' }}</td>
                                    <td class="{{ $edit ? 'text-brand-600' : 'text-black/25' }}">
                                        {{ $edit ? '✓' : '–' }}</td>
                                    <td class="{{ $delete ? 'text-brand-600' : 'text-black/25' }}">
                                        {{ $delete ? '✓' : '–' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </article>

                {{-- Setup aplikasi: kartu biru dengan contoh terminal --}}
                <article
                    class="flex flex-col justify-between gap-8 rounded-[2rem] bg-brand-600 p-8 text-white lg:col-span-2">
                    <div>
                        <h3 class="font-display text-2xl font-medium">Setup aplikasi</h3>
                        <p class="mt-2 text-sm leading-relaxed text-white/80">Dari clone sampai berjalan dalam beberapa
                            perintah.</p>
                    </div>
                    {{-- Ganti <repo-anda> dan perintah sesuai proyek sebenarnya --}}
                    <pre aria-hidden="true" class="overflow-x-auto rounded-2xl bg-black/30 p-4 font-mono text-xs leading-6 text-white/90">$ git clone &lt;repo-anda&gt;
$ composer install
$ php artisan migrate --seed
$ npm run dev</pre>
                </article>

                {{-- Integrasi API: kartu putih dengan contoh permintaan dan respons --}}
                <article class="flex flex-col justify-between gap-8 rounded-[2rem] bg-white p-8 lg:col-span-2">
                    <div>
                        <h3 class="font-display text-2xl font-medium">Integrasi API</h3>
                        <p class="mt-2 text-sm leading-relaxed text-black/70">Endpoint dan format respons yang
                            konsisten untuk frontend terpisah atau aplikasi lain.</p>
                    </div>
                    <div aria-hidden="true" class="rounded-2xl bg-canvas p-4 font-mono text-xs leading-6">
                        <p><span class="rounded bg-brand-100 px-1.5 py-0.5 font-medium text-brand-700">GET</span>
                            /api/v1/users</p>
                        <p class="mt-2 text-black/60">{ "data": [ … ],</p>
                        <p class="text-black/60">&nbsp;&nbsp;"meta": { "total": 24 } }</p>
                    </div>
                </article>

                {{-- Dokumentasi otomatis: kartu berbingkai dengan daftar endpoint --}}
                <article
                    class="flex flex-col justify-between gap-8 rounded-[2rem] border border-brand-200 bg-brand-50 p-8 lg:col-span-2">
                    <div>
                        <h3 class="font-display text-2xl font-medium">Dokumentasi otomatis</h3>
                        <p class="mt-2 text-sm leading-relaxed text-black/70">Tersusun dari kode, jadi tidak tertinggal
                            saat fitur berubah.</p>
                    </div>
                    <ul aria-hidden="true" class="space-y-2 font-mono text-xs">
                        <li class="flex items-center gap-2"><span
                                class="w-12 rounded bg-brand-100 py-0.5 text-center font-medium text-brand-700">GET</span>
                            /users</li>
                        <li class="flex items-center gap-2"><span
                                class="w-12 rounded bg-black py-0.5 text-center font-medium text-white">POST</span>
                            /users</li>
                        <li class="flex items-center gap-2"><span
                                class="w-12 rounded bg-black py-0.5 text-center font-medium text-white">PATCH</span>
                            /users/{id}</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    {{-- ============================================================
         FAQ: akordeon Alpine, satu item terbuka pada satu waktu
         ============================================================ --}}
    <section id="faq" class="bg-white px-6 py-24 lg:px-12">
        <div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-[1fr_1.6fr]">
            <h2 class="font-display text-4xl font-medium leading-[1.05] tracking-tight sm:text-5xl">Pertanyaan yang
                sering muncul</h2>

            <div x-data="{ open: 0 }" class="border-b border-black/15">
                @foreach ($this->faqs as $i => $faq)
                    <div class="border-t border-black/15">
                        <h3>
                            <button type="button"
                                @click="open = open === {{ $i }} ? null : {{ $i }}"
                                :aria-expanded="open === {{ $i }}" aria-controls="faq-{{ $i }}"
                                class="flex w-full items-center justify-between gap-6 py-5 text-left font-display text-xl font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
                                {{ $faq['q'] }}
                                <svg aria-hidden="true" viewBox="0 0 24 24"
                                    class="size-5 shrink-0 transition-transform"
                                    :class="open === {{ $i }} && 'rotate-45'" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                            </button>
                        </h3>
                        <div id="faq-{{ $i }}" x-show="open === {{ $i }}" x-cloak
                            x-transition.opacity>
                            <p class="max-w-xl pb-6 text-sm leading-relaxed text-black/70">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         CTA PENUTUP + FOOTER
         ============================================================ --}}
    <section class="rounded-t-[3rem] bg-black px-6 pb-10 pt-24 text-white lg:px-12">
        <div class="mx-auto max-w-6xl">
            <h2 class="max-w-3xl font-display text-5xl font-medium leading-[0.98] tracking-tight sm:text-7xl">
                Mulai dari fitur pertama Anda.
            </h2>
            <div class="mt-10 flex flex-wrap gap-3">
                <a href="{{ $registerUrl }}"
                    class="{{ $btn }} bg-white text-black hover:bg-brand-100">Mulai sekarang</a>
            </div>

            <footer
                class="mt-24 flex flex-col justify-between gap-2 border-t border-white/15 pt-6 text-xs text-white/60 sm:flex-row">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
                <p>Dibangun dengan Laravel, Livewire, Alpine.js, dan Tailwind CSS.</p>
            </footer>
        </div>
    </section>

    {{-- Memuat font Bricolage Grotesque & Instrument Sans lewat stack 'styles' di layout base --}}
    @push('styles')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..700&family=Instrument+Sans:wght@400;500;600&display=swap"
            rel="stylesheet">
    @endpush
</div>
