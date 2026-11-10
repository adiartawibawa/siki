<?php

use App\Settings\GeneralSettings;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.guest')] #[Title('Boilerplate TALL Stack')] class extends Component {
    /**
     * Bila landing dinonaktifkan lewat config('starter.landing'), pengunjung
     * dialihkan ke halaman masuk (atau beranda aplikasi jika sudah login).
     */
    public function mount()
    {
        $isLandingPage = rescue(fn() => app(GeneralSettings::class)->landing_enabled, false, false);

        if (!$isLandingPage) {
            // Ganti 'dashboard' dengan nama route beranda aplikasi setelah login.
            return $this->redirectRoute(auth()->check() ? 'dashboard' : 'login');
        }
    }

    /**
     * Pertanyaan dan jawaban untuk bagian FAQ (computed: tidak ikut dikirim ke browser).
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
                'a' => 'Autentikasi dengan 2FA, otorisasi berbasis role, halaman pengaturan aplikasi, API dengan token pribadi, dan dokumentasi API yang tersusun otomatis.',
            ],
            [
                'q' => 'Apakah dokumentasi perlu ditulis manual?',
                'a' => 'Untuk API tidak. Dokumen dibuat dari kode aplikasi, sehingga ikut berubah saat Anda menambah atau mengubah endpoint.',
            ],
            [
                'q' => 'Bagaimana cara memulainya?',
                'a' => 'Clone proyek, pasang dependensi dengan Composer dan npm, jalankan migrasi beserta seeder, lalu jalankan server pengembangan. Empat perintahnya ada di bagian Setup aplikasi.',
            ],
            [
                'q' => 'Apakah halaman ini ikut terbit di aplikasi saya?',
                'a' => 'Tidak harus. Matikan lewat pengaturan starter.landing, dan pengunjung langsung diarahkan ke halaman masuk. Setelah itu ganti isinya dengan halaman Anda sendiri.',
            ],
        ];
    }
};
?>

{{-- Livewire mewajibkan satu elemen root --}}
<div>
    @php
        $dashboardUrl = route('dashboard');

        $loginUrl = Route::has('login') ? route('login') : '#';

        // Pendaftaran publik default tertutup (Settings). Tautan daftar hanya dipakai bila benar-benar dibuka,
        // jika tidak tombol utama mengarah ke halaman masuk, bukan ke 404.
        $canRegister =
            Route::has('register') &&
            rescue(fn() => app(\App\Settings\GeneralSettings::class)->registration_enabled, false, false);
        $primaryUrl = $canRegister ? route('register') : $loginUrl;
        $primaryLabel = $canRegister ? 'Mulai sekarang' : 'Masuk';

        $ring = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600';
        $btn = "inline-flex items-center justify-center gap-2 rounded-full px-6 py-3 text-sm font-medium transition $ring";

        // Empat pelat kaca, dari bawah ke atas. 'y' = jarak vertikal, 'delay' = jeda animasi, 'tint' = warna kaca.
        $plates = [
            ['y' => 150, 'delay' => 0, 'tint' => 'from-cyan-300/60 to-brand-500/50'],
            ['y' => 100, 'delay' => 120, 'tint' => 'from-cyan-200/50 to-brand-200/40'],
            ['y' => 50, 'delay' => 240, 'tint' => 'from-white/40 to-cyan-100/30'],
            ['y' => 0, 'delay' => 360, 'tint' => 'from-white/70 to-brand-100/50'],
        ];

        // Legenda, dari atas ke bawah: urutannya sama dengan susunan pelat. [nama, peran, warna swatch]
        $layers = [
            ['Tailwind CSS 4', 'Tampilan', 'bg-brand-100'],
            ['Alpine.js', 'Interaksi di browser', 'bg-cyan-100'],
            ['Livewire 4', 'Komponen dinamis', 'bg-brand-200'],
            ['Laravel 13', 'Backend', 'bg-cyan-400'],
        ];

        // [judul, penjelasan, rincian, kunci contoh visual]
        $features = [
            [
                'Autentikasi',
                'Masuk dengan email atau username, verifikasi email, dan reset password sudah berjalan. 2FA tersedia lengkap dengan kode pemulihan.',
                [
                    'Pendaftaran publik bisa dinyalakan dari pengaturan',
                    'Percobaan masuk dibatasi lajunya',
                    'Akun yang dinonaktifkan tidak bisa masuk',
                ],
                'login',
            ],
            [
                'Otorisasi',
                'Role berjenjang: setiap akun hanya mengelola level di bawahnya. Admin bisa membuat role baru dan memilih permission-nya dari antarmuka.',
                [
                    'Empat role awal, bisa ditambah dari halaman Roles',
                    'Permission hanya bisa dibagikan oleh yang memegangnya',
                    'Aturan dijaga tes untuk setiap kombinasi',
                ],
                'roles',
            ],
            [
                'Setup aplikasi',
                'Nama aplikasi, bahasa, zona waktu, dan surel pengirim diatur dari halaman pengaturan, tanpa menyunting berkas.',
                [
                    'Seeder membuat role dan akun Super Admin pertama',
                    'Halaman Showcase merangkum cara memakai komponen',
                    'Pemeriksaan kesiapan menunjukkan apa yang belum diatur',
                ],
                'setup',
            ],
            [
                'Integrasi API',
                'Endpoint /api/v1 dengan token pribadi. Setiap token dibatasi scope, berlaku 30 sampai 180 hari, dan bisa dicabut kapan saja.',
                [
                    'Akses dikunci per role lewat satu permission',
                    'Token dicabut otomatis saat akun dinonaktifkan',
                    'Dibatasi 60 permintaan per menit',
                ],
                'api',
            ],
            [
                'Dokumentasi otomatis',
                'Dokumen OpenAPI tersusun dari kode lewat Scramble, jadi ikut berubah saat endpoint berubah.',
                [
                    'Dibuka di /docs/api untuk yang berhak',
                    'Bisa diekspor sebagai berkas untuk dibagikan',
                    'Menampilkan skema masukan dan respons',
                ],
                'docs',
            ],
        ];
    @endphp

    {{-- ============================================================
         HERO
         ============================================================ --}}
    <section class="relative isolate overflow-hidden bg-white">

        {{-- Header: logo, navigasi, tombol masuk --}}
        <header x-data="{ menu: false }"
            class="relative z-30 mx-auto flex max-w-6xl items-center justify-between px-6 py-6 lg:px-12">
            <a href="{{ url('/') }}" class="flex items-center gap-2 rounded-md text-slate-900 {{ $ring }}">
                <svg aria-hidden="true" viewBox="0 0 24 24" class="size-7 text-brand-600" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linejoin="round">
                    <path d="M12 3l9 4.5-9 4.5-9-4.5z" />
                    <path d="M3 12l9 4.5 9-4.5" />
                    <path d="M3 16.5L12 21l9-4.5" />
                </svg>
                <span class="font-display text-xl font-medium tracking-wide">{{ config('app.name') }}</span>
            </a>

            <nav aria-label="Navigasi utama" class="hidden items-center gap-10 text-sm text-slate-600 lg:flex">
                <a href="#fitur" class="rounded-sm hover:text-slate-900 {{ $ring }}">Fitur</a>
                <a href="#faq" class="rounded-sm hover:text-slate-900 {{ $ring }}">FAQ</a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ $dashboardUrl }}"
                        class="{{ $btn }} border border-slate-300 text-slate-900 hover:bg-slate-900 hover:text-white max-lg:hidden">Dashboard</a>
                @else
                    <a href="{{ $loginUrl }}"
                        class="{{ $btn }} border border-slate-300 text-slate-900 hover:bg-slate-900 hover:text-white max-lg:hidden">Masuk</a>
                @endauth

                <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-controls="menu-mobile"
                    aria-label="Buka menu"
                    class="grid size-11 place-items-center rounded-full border border-slate-300 text-slate-900 lg:hidden {{ $ring }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round">
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
            </div>

            <div id="menu-mobile" x-show="menu" x-cloak @click.outside="menu = false"
                class="absolute inset-x-6 top-20 rounded-3xl bg-white p-6 text-slate-900 shadow-xl ring-1 ring-black/10 lg:hidden">
                <nav aria-label="Navigasi mobile" class="flex flex-col gap-4 text-base">
                    <a href="#fitur" @click="menu = false">Fitur</a>
                    <a href="#faq" @click="menu = false">FAQ</a>
                    @auth
                        <a href="{{ $dashboardUrl }}" class="font-medium">Dashboard</a>
                    @else
                        <a href="{{ $loginUrl }}" class="font-medium">Masuk</a>
                    @endauth
                </nav>
            </div>
        </header>

        <div class="mx-auto grid max-w-6xl gap-12 px-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:gap-20 lg:px-12">

            {{-- Teks hero --}}
            <div class="pt-6 lg:self-center lg:pb-24 lg:pt-8">
                <h1
                    class="max-w-3xl text-balance font-display text-5xl font-medium leading-[1] tracking-tight text-slate-900 sm:text-6xl xl:text-7xl">
                    Bangun aplikasi Laravel tanpa mulai dari nol.
                </h1>

                <p class="mt-8 max-w-md text-base leading-relaxed text-slate-600">
                    Boilerplate TALL Stack dengan autentikasi, otorisasi, setup aplikasi, integrasi API, dan dokumentasi
                    otomatis yang sudah terpasang.
                </p>

                <div class="mt-10 flex flex-wrap items-center gap-x-8 gap-y-4">
                    <a href="{{ $primaryUrl }}"
                        class="{{ $btn }} bg-slate-900 text-white hover:bg-brand-700">
                        {{ $primaryLabel }}
                    </a>
                    <a href="#fitur"
                        class="inline-flex items-center gap-2 rounded-sm text-sm font-medium text-slate-700 hover:text-slate-950 {{ $ring }}">
                        Lihat fitur
                        <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M6 13l6 6 6-6" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Satu-satunya elemen yang bergerak: lengkung biru berisi empat pelat kaca.
                 Berdiri tepat di tepi bawah hero; animasinya jalan sekali saat halaman dimuat. --}}
            <div aria-hidden="true"
                class="relative mx-auto h-[36rem] w-full max-w-[22rem] self-end rounded-t-full bg-brand-600 lg:mx-0 lg:h-[38rem] lg:w-[24rem] lg:max-w-none">

                <div class="absolute left-1/2 top-[28%] -translate-x-1/2 -translate-y-1/2 lg:top-[30%]">
                    @foreach ($plates as $plate)
                        <div style="--y: {{ $plate['y'] }}px; animation-delay: {{ $plate['delay'] }}ms"
                            class="absolute -left-28 -top-28 size-56 animate-plate-in rounded-4xl border border-white/80 bg-linear-to-br lg:-left-32 lg:-top-32 lg:size-64 {{ $plate['tint'] }}
                                   shadow-[inset_0_0_32px_rgba(255,255,255,0.35),0_10px_0_-2px_rgba(255,255,255,0.25)] backdrop-blur-[2px]
                                   [transform:translateY(var(--y))_rotateX(62deg)_rotateZ(45deg)] motion-reduce:animate-none">
                        </div>
                    @endforeach
                </div>

                {{-- Legenda: swatch memakai warna pelatnya, urutan sama dengan susunan pelat --}}
                <div class="absolute inset-x-5 bottom-5 rounded-2xl bg-white p-4 text-slate-900 shadow-lg">
                    <p class="font-display text-base font-medium">TALL Stack</p>
                    <ul class="mt-2 space-y-1.5">
                        @foreach ($layers as [$name, $role, $swatch])
                            <li class="flex items-center gap-3 text-sm">
                                <span
                                    class="size-3 shrink-0 rounded-sm ring-1 ring-black/15 {{ $swatch }}"></span>
                                <span class="font-medium">{{ $name }}</span>
                                <span class="ml-auto text-xs text-slate-600">{{ $role }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         FITUR: lima bagian yang sudah terpasang
         ============================================================ --}}
    <section id="fitur" class="bg-canvas px-6 py-24 lg:px-12">
        <div class="mx-auto max-w-6xl">
            <h2
                class="max-w-3xl text-balance font-display text-4xl font-medium leading-[1.05] tracking-tight text-slate-900 sm:text-5xl">
                Lima bagian yang selalu dibangun ulang, sudah terpasang.
            </h2>

            <div class="mt-16">
                @foreach ($features as [$title, $text, $items, $preview])
                    <article
                        class="grid items-center gap-10 border-t border-slate-300/70 py-12 first:border-t-0 lg:grid-cols-2 lg:gap-20 lg:py-16">
                        <div>
                            <h3 class="font-display text-2xl font-medium text-slate-900 sm:text-3xl">
                                {{ $title }}</h3>
                            <p class="mt-3 max-w-md text-base leading-relaxed text-slate-600">{{ $text }}</p>

                            <ul class="mt-6 space-y-2.5 text-sm text-slate-700">
                                @foreach ($items as $item)
                                    <li class="flex items-start gap-3">
                                        <svg aria-hidden="true" viewBox="0 0 24 24"
                                            class="mt-0.5 size-4 shrink-0 text-brand-600" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ $item }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Contoh tampilan: dekoratif, sehingga disembunyikan dari pembaca layar --}}
                        <div aria-hidden="true" class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-8">
                            @switch($preview)
                                @case('login')
                                    <div class="mx-auto w-full max-w-xs space-y-3">
                                        <p class="font-display text-lg font-medium text-slate-900">Welcome back</p>
                                        <div class="rounded-lg border border-slate-200 px-3 py-2.5">
                                            <p class="text-[11px] text-slate-500">Email or username</p>
                                            <p class="text-sm text-slate-900">nama@perusahaan.com</p>
                                        </div>
                                        <div class="rounded-lg border border-slate-200 px-3 py-2.5">
                                            <p class="text-[11px] text-slate-500">Password</p>
                                            <p class="text-sm tracking-widest text-slate-900">••••••••</p>
                                        </div>
                                        <div
                                            class="rounded-full bg-brand-600 py-2.5 text-center text-sm font-medium text-white">
                                            Log in</div>
                                    </div>
                                @break

                                @case('roles')
                                    <table class="w-full text-left text-sm">
                                        <thead>
                                            <tr class="text-xs text-slate-500">
                                                <th class="pb-2 font-normal">Role</th>
                                                <th class="pb-2 font-normal">Level</th>
                                                <th class="pb-2 font-normal">Can manage</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-slate-900">
                                            @foreach ([['Super Admin', 100, 'Everyone'], ['Admin', 80, 'Below level 80'], ['Operator', 50, 'Below level 50'], ['Regular User', 10, 'No one']] as [$role, $level, $manages])
                                                <tr class="border-t border-slate-200">
                                                    <td class="py-3 font-medium">{{ $role }}</td>
                                                    <td class="py-3 tabular-nums text-brand-700">{{ $level }}</td>
                                                    <td class="py-3 text-slate-600">{{ $manages }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @break

                                @case('setup')
                                    {{-- Ganti <repo-anda> dan perintah sesuai proyek sebenarnya --}}
                                    <pre class="overflow-x-auto rounded-2xl bg-slate-900 p-5 font-mono text-xs leading-7 text-slate-100">$ git clone &lt;repo-anda&gt;
$ composer install
$ php artisan migrate --seed
$ npm run dev</pre>
                                @break

                                @case('api')
                                    <div class="space-y-3 font-mono text-xs leading-6 text-slate-700">
                                        <p><span
                                                class="rounded bg-brand-100 px-1.5 py-0.5 font-medium text-brand-700">GET</span>
                                            /api/v1/users</p>
                                        <p class="text-slate-500">Authorization: Bearer ••••••••</p>
                                        <pre class="rounded-2xl bg-slate-100 p-4 text-slate-700">{
  "data": [ … ],
  "meta": { "total": 24 }
}</pre>
                                    </div>
                                @break

                                @case('docs')
                                    <ul class="divide-y divide-slate-200 text-sm">
                                        @foreach ([['/api/v1/users', 'List accounts you may see'], ['/api/v1/users/{user}', 'Show one account'], ['/api/v1/roles', 'List roles, highest level first']] as [$path, $summary])
                                            <li class="flex flex-wrap items-center gap-x-3 gap-y-1 py-3 first:pt-0 last:pb-0">
                                                <span
                                                    class="w-12 rounded bg-brand-100 py-0.5 text-center font-mono text-xs font-medium text-brand-700">GET</span>
                                                <span class="font-mono text-xs text-slate-900">{{ $path }}</span>
                                                <span class="text-xs text-slate-500 sm:ml-auto">{{ $summary }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @break
                            @endswitch
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         FAQ: akordeon Alpine, satu item terbuka pada satu waktu
         ============================================================ --}}
    <section id="faq" class="bg-white px-6 py-24 lg:px-12">
        <div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-[1fr_1.6fr]">
            <h2
                class="text-balance font-display text-4xl font-medium leading-[1.05] tracking-tight text-slate-900 sm:text-5xl">
                Pertanyaan yang sering muncul
            </h2>

            <div x-data="{ open: 0 }" class="border-b border-slate-200">
                @foreach ($this->faqs as $i => $faq)
                    <div class="border-t border-slate-200">
                        <h3>
                            <button type="button"
                                @click="open = open === {{ $i }} ? null : {{ $i }}"
                                :aria-expanded="open === {{ $i }}" aria-controls="faq-{{ $i }}"
                                class="flex w-full items-center justify-between gap-6 rounded-sm py-5 text-left font-display text-xl font-medium text-slate-900 {{ $ring }}">
                                {{ $faq['q'] }}
                                <svg aria-hidden="true" viewBox="0 0 24 24"
                                    class="size-5 shrink-0 transition-transform motion-reduce:transition-none"
                                    :class="open === {{ $i }} && 'rotate-45'" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                            </button>
                        </h3>
                        <div id="faq-{{ $i }}" x-show="open === {{ $i }}" x-cloak
                            x-transition.opacity>
                            <p class="max-w-xl pb-6 text-sm leading-relaxed text-slate-600">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         CTA PENUTUP + FOOTER
         ============================================================ --}}
    <section class="rounded-t-[2.5rem] bg-brand-800 px-6 pb-10 pt-24 text-white lg:px-12">
        <div class="mx-auto max-w-6xl">
            <h2
                class="max-w-3xl text-balance font-display text-5xl font-medium leading-[1] tracking-tight sm:text-6xl">
                Mulai dari fitur pertama Anda.
            </h2>

            <div class="mt-10">
                <a href="{{ $primaryUrl }}"
                    class="{{ $btn }} bg-white text-brand-800 hover:bg-brand-100">
                    {{ $primaryLabel }}
                </a>
            </div>

            <footer
                class="mt-24 flex flex-col justify-between gap-2 border-t border-white/20 pt-6 text-xs text-white/75 sm:flex-row">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
                <p>Dibangun dengan Laravel, Livewire, Alpine.js, dan Tailwind CSS.</p>
            </footer>
        </div>
    </section>

    {{-- Font: Bricolage Grotesque (judul) dan Instrument Sans (isi), lewat stack 'styles' di layout base --}}
    @push('styles')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..700&family=Instrument+Sans:wght@400;500;600&display=swap"
            rel="stylesheet">
    @endpush
</div>
