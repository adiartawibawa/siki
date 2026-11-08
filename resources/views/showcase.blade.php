@php
    $toc = [
        'kesiapan' => 'Kesiapan',
        'mulai' => 'Memulai proyek',
        'peta' => 'Peta berkas',
        'menu' => 'Menu sidebar',
        'halaman' => 'Halaman baru',
        'otorisasi' => 'Otorisasi',
        'setelan' => 'Setelan aplikasi',
        'komponen' => 'Komponen UI',
        'notifikasi' => 'Notifikasi',
        'turunan' => 'Aplikasi turunan',
        'rilis' => 'Sebelum rilis',
    ];

    // Rute di config/admin.php yang tidak terdaftar (item yang dimatikan 'when' tidak dihitung).
    $missingRoutes = collect(config('admin.nav'))
        ->pluck('items')
        ->flatten(1)
        ->flatMap(fn($i) => $i['children'] ?? [$i])
        ->reject(fn($i) => isset($i['when']) && !config($i['when']))
        ->pluck('route')
        ->filter()
        ->reject(fn($r) => Route::has($r))
        ->values();

    $abilities = array_map(fn($a) => $a->value, \App\Enums\Ability::cases());
    $roleNames = array_map(fn($r) => $r->value, \App\Enums\UserRole::cases());

    // [label, uji, petunjuk]. Uji yang melempar exception dianggap gagal.
    $checks = [
        [
            'Role dan permission sudah di-seed',
            fn() => \App\Models\Role::whereIn('name', $roleNames)->count() === count($roleNames) &&
                \Spatie\Permission\Models\Permission::whereIn('name', $abilities)->count() === count($abilities),
            'php artisan db:seed --class=RolePermissionSeeder',
        ],
        [
            'Ada akun Super Admin',
            fn() => \App\Models\User::role(\App\Enums\UserRole::SuperAdmin->value)->exists(),
            'Isi INITIAL_ADMIN_EMAIL dan INITIAL_ADMIN_PASSWORD lalu seed, atau assignRole lewat tinker',
        ],
        [
            'Pivot Spatie memakai UUID (model_has_roles.model_id)',
            fn() => !str_contains(
                strtolower(
                    collect(\Illuminate\Support\Facades\Schema::getColumns('model_has_roles'))->firstWhere(
                        'name',
                        'model_id',
                    )['type_name'] ?? 'int',
                ),
                'int',
            ),
            'Ubah model_morph_key menjadi uuid di migrasi Spatie sebelum migrate',
        ],
        [
            'Tabel setelan ada dan terisi',
            fn() => \Illuminate\Support\Facades\Schema::hasTable('settings') &&
                \Illuminate\Support\Facades\DB::table('settings')->where('group', 'general')->exists(),
            'php artisan migrate',
        ],
        [
            'Kolom 2FA ada di tabel users',
            fn() => \Illuminate\Support\Facades\Schema::hasColumn('users', 'two_factor_secret'),
            'Jalankan migrasi Fortify untuk kolom two_factor_*',
        ],
        [
            'Semua route di config/admin.php terdaftar',
            fn() => $missingRoutes->isEmpty(),
            'Item tanpa route disembunyikan otomatis, jadi salah ketik tidak menimbulkan error. Belum terdaftar: ' .
            $missingRoutes->implode(', '),
        ],
        [
            'APP_TIMEZONE = UTC',
            fn() => config('app.timezone') === 'UTC',
            'Zona waktu penyimpanan tidak boleh berubah di tengah jalan; zona tampilan diatur di Settings',
        ],
        [
            'Sesi di database (untuk daftar sesi browser)',
            fn() => config('session.driver') === 'database',
            'Opsional: SESSION_DRIVER=database. Tanpanya bagian Browser sessions disembunyikan',
        ],
        [
            'Siap production: debug mati, build aset ada, mailer dan queue bukan log atau sync',
            fn() => !app()->isProduction() ||
                (!config('app.debug') &&
                    file_exists(public_path('build/manifest.json')) &&
                    config('mail.default') !== 'log' &&
                    config('queue.default') !== 'sync'),
            'Hanya dinilai di production',
        ],
        [
            'Showcase tidak aktif di production',
            fn() => !(app()->isProduction() && config('admin.showcase')),
            'Set SHOWCASE_ENABLED=false',
        ],
    ];

    $sfc = <<<'CODE'
    <?php
    
    use App\Livewire\Concerns\Notifies;
    use App\Models\Contoh;
    use Livewire\Attributes\Layout;
    use Livewire\Attributes\Title;
    use Livewire\Component;
    
    new #[Layout('layouts.app')] #[Title('Contoh')] class extends Component {
        use Notifies;
    
        public function mount(): void
        {
            $this->authorize('viewAny', Contoh::class);
        }
    
        public function save(Contoh $contoh): void
        {
            // Setiap aksi adalah request tersendiri: otorisasi ulang di sini.
            $this->authorize('update', $contoh);
    
            // ... simpan ...
    
            $this->notify('Tersimpan.');
        }
    };
    ?>

    <div class="space-y-4">
        {{-- isi halaman --}}
    </div>
    CODE;
@endphp

<x-layouts::app title="Showcase">
    <x-ui.card>
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Showcase Core</h2>
        <p class="mt-2 max-w-[70ch] text-sm leading-relaxed text-slate-600">
            Pengingat untuk developer saat memakai Core membuat aplikasi tertentu. Contoh komponen di sini dirender
            dari kode yang sama dengan yang ditampilkan, jadi kalau API sebuah komponen berubah, bagian itu akan
            menampilkan galat. Keputusan dan alasannya ada di <code
                class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">DECISIONS.md</code>,
            kebutuhan di <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">REQUIREMENTS.md</code>.
            Halaman ini hanya terdaftar bila <code
                class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">SHOWCASE_ENABLED</code> aktif (bawaan: lingkungan
            local).
        </p>

        <nav aria-label="Daftar isi" class="mt-5 flex flex-wrap gap-2">
            @foreach ($toc as $id => $label)
                <a href="#{{ $id }}"
                    class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </x-ui.card>

    {{-- Kesiapan --}}
    <x-showcase.section id="kesiapan" title="Pemeriksaan kesiapan"
        intro="Daftar hal yang paling sering terlupa saat memulai atau memindahkan Core. Semua uji dijalankan saat halaman dibuka.">
        <ul class="divide-y divide-slate-100">
            @foreach ($checks as [$label, $test, $hint])
                @php $ok = rescue($test, false, false); @endphp
                <li class="flex items-start gap-3 py-3">
                    <span class="mt-0.5">
                        <x-ui.badge :tone="$ok ? 'success' : 'warning'">{{ $ok ? 'OK' : 'Cek' }}</x-ui.badge>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-900">{{ $label }}</p>
                        @unless ($ok)
                            <p class="mt-0.5 break-words text-xs text-slate-500">{{ $hint }}</p>
                        @endunless
                    </div>
                </li>
            @endforeach
        </ul>
    </x-showcase.section>

    {{-- Memulai --}}
    <x-showcase.section id="mulai" title="Memulai proyek baru"
        intro="Urutan setelah clone. Ganti nama, aset, dan salinan teks sebelum menambah fitur.">
        <x-showcase.code lang="bash">
            @verbatim
                git clone <repo> aplikasi-baru && cd aplikasi-baru
                    composer install && npm install
                    cp .env.example .env && php artisan key:generate

                    # isi DB_*, lalu (opsional) INITIAL_ADMIN_EMAIL dan INITIAL_ADMIN_PASSWORD
                    php artisan migrate --seed
                    npm run dev

                    ./vendor/bin/pest # semua tes harus lulus sebelum mulai mengubah aturan
                @endverbatim
        </x-showcase.code>
        <ul class="list-disc space-y-1.5 pl-5 text-sm leading-relaxed text-slate-700">
            <li>Migrasi pivot Spatie harus memakai <code class="rounded bg-slate-100 px-1 text-xs">uuid</code> sebelum
                <code class="rounded bg-slate-100 px-1 text-xs">migrate</code> (lihat pemeriksaan di atas).
            </li>
            <li>Seeder membuat role sistem sekali dan tidak menimpa hasil edit di UI. <code
                    class="rounded bg-slate-100 px-1 text-xs">Ability</code> baru tidak otomatis sampai ke Admin atau
                Operator.</li>
            <li>Pendaftaran publik default tertutup. Akun dibuat admin dan memilih password sendiri lewat tautan di
                email.</li>
        </ul>
    </x-showcase.section>

    {{-- Peta berkas --}}
    <x-showcase.section id="peta" title="Peta berkas" intro="Di mana mencari apa.">
        <x-showcase.code lang="text">
            @verbatim
                app/
                ├─ Actions/Fortify/ CreateNewUser, ResetUserPassword
                ├─ Enums/ UserRole (role awal), Ability (daftar permission)
                ├─ Livewire/Concerns/ Notifies (toast dari komponen)
                ├─ Models/ User (level, visibleTo), Role (level, is_system)
                ├─ Policies/ UserPolicy, RolePolicy ← aturan hierarki di sini
                ├─ Providers/ FortifyServiceProvider, AppServiceProvider
                ├─ Settings/ GeneralSettings, MailSettings
                └─ Support/Navigation.php menyaring menu sidebar
                config/admin.php menu, promo, akun awal, showcase
                resources/views/
                ├─ layouts/ base, guest, app
                ├─ components/
                │ ├─ admin/ sidebar, nav-item, topbar, promo, save-bar
                │ ├─ account/tabs
                │ ├─ auth/shell
                │ ├─ form/ input, select, toggle
                │ ├─ ui/ button, card, badge, alert, dropdown, modal, ...
                │ ├─ showcase/ khusus halaman ini
                │ └─ errors/page
                ├─ auth/ halaman Fortify (Blade biasa, form POST)
                ├─ errors/ 401 … 503
                └─ pages/ account, users, roles, settings (Livewire)
                database/seeders/RolePermissionSeeder.php
                tests/Feature/ policy, akses, auth, navigasi
            @endverbatim
        </x-showcase.code>
    </x-showcase.section>

    {{-- Menu --}}
    <x-showcase.section id="menu" title="Menu sidebar"
        intro="Menu dibaca dari config/admin.php dan disaring oleh App\Support\Navigation. Item tampil hanya bila route-nya terdaftar, lolos 'can', dan flag 'when' aktif.">
        <x-showcase.code lang="php">
            @verbatim
                // config/admin.php → 'nav' => [ [ 'title' => ..., 'items' => [ ... ] ] ]
                ['label' => 'Laporan', 'icon' => 'file-text', 'route' => 'laporan.index', 'can' => 'laporan.view'],

                [
                'label' => 'Master data',
                'icon' => 'database',
                'can' => 'master.view', // menyembunyikan seluruh induk
                'children' => [
                ['label' => 'Wilayah', 'route' => 'wilayah.index'],
                ['label' => 'Kategori', 'route' => 'kategori.index', 'match' => 'kategori.*'],
                ],
                ],
            @endverbatim
        </x-showcase.code>
        <ul class="list-disc space-y-1.5 pl-5 text-sm leading-relaxed text-slate-700">
            <li><code class="rounded bg-slate-100 px-1 text-xs">icon</code> adalah nama berkas di <code
                    class="rounded bg-slate-100 px-1 text-xs">resources/svg/lucide</code>. Nama yang salah tidak
                menimbulkan galat, hanya komentar HTML.</li>
            <li>Salah ketik nama route membuat item hilang tanpa pesan. Pemeriksaan "Semua route terdaftar" di atas ada
                untuk itu.</li>
            <li>Menu hanya mengatur tampilan. Otorisasi sebenarnya ada di route dan di dalam aksi komponen.</li>
        </ul>
    </x-showcase.section>

    {{-- Halaman baru --}}
    <x-showcase.section id="halaman" title="Menambah halaman"
        intro="Pola yang dipakai Users dan Roles: route yang dijaga policy, komponen Livewire, otorisasi ulang di setiap aksi.">
        <x-showcase.code lang="php">
            @verbatim
                // routes/web.php
                Route::middleware(['auth', 'can:viewAny,' . \App\Models\Contoh::class])->group(function () {
                Route::livewire('/contoh', 'pages::contoh.index')->name('contoh.index');
                });
            @endverbatim
        </x-showcase.code>
        <x-showcase.code lang="resources/views/pages/contoh/⚡index.blade.php">{{ $sfc }}</x-showcase.code>
        <ul class="list-disc space-y-1.5 pl-5 text-sm leading-relaxed text-slate-700">
            <li>Tautan internal diberi <code class="rounded bg-slate-100 px-1 text-xs">wire:navigate</code> agar ada bar
                progres. Form biasa (logout) memuat ulang halaman penuh.</li>
            <li>Setelah aksi yang berakhir redirect, kirim pesan lewat <code
                    class="rounded bg-slate-100 px-1 text-xs">session()->flash('toast', …)</code>, bukan <code
                    class="rounded bg-slate-100 px-1 text-xs">notify()</code>.</li>
        </ul>
    </x-showcase.section>

    {{-- Otorisasi --}}
    <x-showcase.section id="otorisasi" title="Otorisasi"
        intro="Spatie Permission dengan role di database. Enum UserRole dan Ability hanya data awal; role tambahan dibuat lewat UI.">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[32rem] text-left text-sm text-slate-700">
                <thead class="text-slate-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Role awal</th>
                        <th class="py-2 pr-4 font-medium">Level</th>
                        <th class="py-2 font-medium">Boleh mengelola</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-2 pr-4">Super Admin</td>
                        <td class="pr-4">100</td>
                        <td>Semua akun, termasuk Super Admin lain</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4">Admin</td>
                        <td class="pr-4">80</td>
                        <td>Level di bawah 80, plus setelan aplikasi</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4">Operator</td>
                        <td class="pr-4">50</td>
                        <td>Level di bawah 50</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4">Regular User</td>
                        <td class="pr-4">10</td>
                        <td>Tidak ada</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <ul class="list-disc space-y-1.5 pl-5 text-sm leading-relaxed text-slate-700">
            <li>Aturannya "hanya di bawah": level yang sama tidak boleh saling mengelola. Tidak ada yang mengubah
                akunnya sendiri lewat halaman Users; itu lewat Account.</li>
            <li>Role yang diberikan harus berlevel di bawah pemberinya, dan permission yang dibagikan harus dipegang
                pemberinya.</li>
            <li>Gunakan <code class="rounded bg-slate-100 px-1 text-xs">$user->can('nama.permission')</code>, bukan
                <code class="rounded bg-slate-100 px-1 text-xs">hasPermissionTo()</code>: yang kedua tidak melewati
                Gate, sehingga Super Admin ikut ditolak.
            </li>
        </ul>
        <x-showcase.code lang="langkah menambah permission">
            @verbatim
                1. Tambah case di App\Enums\Ability (plus label() dan group()).
                2. Masukkan ke UserRole::abilities() untuk role sistem yang berhak.
                3. php artisan db:seed --class=RolePermissionSeeder
                (role lama tidak ditimpa: berikan lewat halaman Roles atau migrasi data)
                4. Jaga dengan route ->middleware('can:...') dan authorize() di aksi komponen.
                5. Tambah baris di tes matriks policy sebelum mengubah kodenya.
            @endverbatim
        </x-showcase.code>
    </x-showcase.section>

    {{-- Setelan --}}
    <x-showcase.section id="setelan" title="Setelan aplikasi"
        intro="spatie/laravel-settings untuk nilai yang boleh diubah admin lewat UI. Rahasia dan kredensial tetap di .env.">
        <x-showcase.code lang="php">
            @verbatim
                // 1. app/Settings/GeneralSettings.php → tambah properti
                public ?string $footer_text;

                // 2. database/settings/xxxx_add_footer_text.php → migrasi nilai awal
                $this->migrator->add('general.footer_text', null);

                // 3. resources/views/pages/settings/⚡general.blade.php
                // tambah properti di komponen, field di form, dan aturan validasi di save()

                // Membaca di mana saja
                app(\App\Settings\GeneralSettings::class)->footer_text;
            @endverbatim
        </x-showcase.code>
        <ul class="list-disc space-y-1.5 pl-5 text-sm leading-relaxed text-slate-700">
            <li>Properti tanpa migrasi nilai awal akan melempar <code
                    class="rounded bg-slate-100 px-1 text-xs">MissingSettings</code>.</li>
            <li>Di production, aktifkan cache setelan (<code
                    class="rounded bg-slate-100 px-1 text-xs">SETTINGS_CACHE_ENABLED</code>). Dengan cache aktif,
                perubahan langsung di database butuh <code class="rounded bg-slate-100 px-1 text-xs">php artisan
                    settings:clear-cache</code>.</li>
        </ul>
    </x-showcase.section>

    {{-- Komponen --}}
    <x-showcase.section id="komponen" title="Komponen UI"
        intro="Semua komponen ada di resources/views/components. Warna utama diatur lewat token brand-* di resources/css/app.css.">

        <x-showcase.example title="Tombol (ui.button)">
            @verbatim
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button variant="primary" icon="plus">Primary</x-ui.button>
                    <x-ui.button variant="dark">Dark</x-ui.button>
                    <x-ui.button>Outline</x-ui.button>
                    <x-ui.button variant="ghost">Ghost</x-ui.button>
                    <x-ui.button variant="danger" icon="trash-2">Danger</x-ui.button>
                    <x-ui.button size="sm">Small</x-ui.button>
                    <x-ui.button size="icon" pill aria-label="Notifikasi"><x-ui.icon name="bell"
                            size="sm" /></x-ui.button>
                    <x-ui.button href="#kesiapan">Sebagai tautan</x-ui.button>
                </div>
            @endverbatim
        </x-showcase.example>

        <x-showcase.example title="Ikon (ui.icon)">
            @verbatim
                <div class="flex items-center gap-4 text-slate-700">
                    <x-ui.icon name="check" size="xs" />
                    <x-ui.icon name="check" size="sm" />
                    <x-ui.icon name="check" />
                    <x-ui.icon name="check" size="lg" color="text-emerald-600" />
                    <x-ui.icon name="check" size="32" color="#4f46e5" />
                </div>
            @endverbatim
        </x-showcase.example>

        <x-showcase.example title="Badge dan alert">
            @verbatim
                <div class="flex flex-wrap gap-2">
                    <x-ui.badge tone="success">Active</x-ui.badge>
                    <x-ui.badge tone="warning">Unverified</x-ui.badge>
                    <x-ui.badge tone="danger">Failed</x-ui.badge>
                    <x-ui.badge tone="brand">Admin</x-ui.badge>
                    <x-ui.badge tone="neutral" :dot="false">Tanpa titik</x-ui.badge>
                </div>
                <div class="mt-4 space-y-3">
                    <x-ui.alert tone="info" title="Info">Pesan yang harus tetap terlihat di halaman.</x-ui.alert>
                    <x-ui.alert tone="danger" dismissible>Gagal menyimpan.</x-ui.alert>
                </div>
            @endverbatim
        </x-showcase.example>

        <x-showcase.example title="Toast">
            @verbatim
                <div class="flex flex-wrap gap-2">
                    <x-ui.button
                        @click="$dispatch('toast', { type: 'success', message: 'Perubahan disimpan.' })">Success</x-ui.button>
                    <x-ui.button
                        @click="$dispatch('toast', { type: 'info', title: 'Info', message: 'Judul bersifat opsional.' })">Info</x-ui.button>
                    <x-ui.button
                        @click="$dispatch('toast', { type: 'warning', message: 'Hilang sendiri setelah 8 detik.' })">Warning</x-ui.button>
                    <x-ui.button
                        @click="$dispatch('toast', { type: 'danger', message: 'Error menetap sampai ditutup.' })">Danger</x-ui.button>
                </div>
            @endverbatim
        </x-showcase.example>

        <x-showcase.example title="Field form (form.input, form.select, form.toggle)">
            @verbatim
                <div class="grid max-w-md gap-4">
                    <x-form.input label="Nama" name="demo_nama" placeholder="Nama lengkap" />
                    <x-form.input label="Kata sandi" name="demo_password" type="password" />
                    <x-form.input label="Catatan" name="demo_catatan" :optional="true" />
                    <x-form.select label="Peran" name="demo_peran" :options="['a' => 'Operator', 'b' => 'Regular User']" />
                    <x-form.toggle label="Aktifkan fitur" description="Penjelasan singkat di bawah label." />
                </div>
            @endverbatim
        </x-showcase.example>

        <x-showcase.example title="Dropdown">
            @verbatim
                <x-ui.dropdown width="w-44"
                    trigger-class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-800 hover:bg-slate-50">
                    <x-slot:trigger>Aksi <x-ui.icon name="chevron-down" size="sm" /></x-slot:trigger>
                    <x-ui.dropdown-item icon="pencil">Ubah</x-ui.dropdown-item>
                    <x-ui.dropdown-item icon="trash-2" danger>Hapus</x-ui.dropdown-item>
                </x-ui.dropdown>
            @endverbatim
        </x-showcase.example>

        <x-showcase.example title="Modal (dialog bawaan browser)">
            @verbatim
                <x-ui.button @click="$dispatch('modal-open-contoh')">Buka modal</x-ui.button>

                <x-ui.modal name="contoh" title="Contoh modal" description="Tutup dengan Escape atau klik di luar.">
                    <div class="flex justify-end">
                        <x-ui.button @click="$dispatch('modal-close-contoh')">Tutup</x-ui.button>
                    </div>
                </x-ui.modal>
            @endverbatim
        </x-showcase.example>

        <x-showcase.example title="Segmented, progress, stat card">
            @verbatim
                <x-ui.segmented :options="['bulan' => 'Bulanan', 'tahun' => 'Tahunan']" value="bulan" aria-label="Periode" />

                <x-ui.progress class="mt-4" :value="62" label="Kuota terpakai" />

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <x-ui.stat-card icon="users" tone="violet" value="1.248" caption="Akun aktif"
                        label="Pengguna" />
                    <x-ui.stat-card icon="wallet" tone="orange" value="3,2 jt" caption="Contoh nominal"
                        label="Pendapatan" />
                </div>
            @endverbatim
        </x-showcase.example>

        <x-showcase.example title="Tabel dan avatar">
            @verbatim
                <x-ui.table>
                    <x-slot:head>
                        <th scope="col">Nama</th>
                        <th scope="col">Status</th>
                    </x-slot:head>
                    <tr>
                        <td><span class="flex items-center gap-3"><x-ui.avatar name="Ayu Lestari" size="sm" /> Ayu
                                Lestari</span></td>
                        <td><x-ui.badge tone="success">Active</x-ui.badge></td>
                    </tr>
                    <tr>
                        <td><span class="flex items-center gap-3"><x-ui.avatar name="Made Wirawan" size="sm" /> Made
                                Wirawan</span></td>
                        <td><x-ui.badge tone="warning">Unverified</x-ui.badge></td>
                    </tr>
                </x-ui.table>
            @endverbatim
        </x-showcase.example>

        <x-showcase.example title="Grafik garis (SVG dari server)">
            @verbatim
                <x-ui.line-chart :labels="['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']" :series="[
                    ['name' => 'Masuk', 'tone' => 'brand', 'data' => [10, 24, 18, 32, 28, 40]],
                    ['name' => 'Keluar', 'tone' => 'teal', 'data' => [6, 12, 14, 18, 22, 20]],
                ]" :highlight="3" prefix="" />
            @endverbatim
        </x-showcase.example>
    </x-showcase.section>

    {{-- Notifikasi --}}
    <x-showcase.section id="notifikasi" title="Notifikasi"
        intro="Toast untuk konfirmasi tindakan, alert inline untuk pesan yang harus tetap terlihat di halaman.">
        <x-showcase.code lang="php">
            @verbatim
                // Di komponen Livewire (trait App\Livewire\Concerns\Notifies)
                $this->notify('Tersimpan.'); // tetap di halaman
                $this->notify('Gagal mengirim.', 'danger'); // error menetap

                // Sebelum redirect (komponen lama hilang bersama halamannya)
                session()->flash('toast', ['type' => 'success', 'message' => 'Akun dihapus.']);

                // Dari JavaScript atau Alpine
                window.toast({ type: 'info', message: '...' });
            @endverbatim
        </x-showcase.code>
    </x-showcase.section>

    {{-- Turunan --}}
    <x-showcase.section id="turunan" title="Aplikasi turunan"
        intro="Core sengaja tidak punya Units atau pembatasan data per organisasi. Itu ditambahkan per aplikasi lewat satu titik sambung di model User.">
        <x-showcase.code lang="php">
            @verbatim
                // app/Models/User.php: ubah KEDUANYA bersama, harus konsisten
                public function scopeVisibleTo(Builder $query, User $actor): void
                {
                $query->whereIn('unit_id', $actor->unitIdsWithDescendants());
                }

                public function isVisibleTo(User $actor): bool
                {
                return $actor->unitIdsWithDescendants()->contains($this->unit_id);
                }
            @endverbatim
        </x-showcase.code>
        <ul class="list-disc space-y-1.5 pl-5 text-sm leading-relaxed text-slate-700">
            <li>Scope menyaring <strong>daftar</strong>, <code
                    class="rounded bg-slate-100 px-1 text-xs">isVisibleTo</code> menyaring <strong>akses langsung per
                    id</strong> lewat policy. Lupa salah satunya adalah kebocoran data.</li>
            <li>Halaman Users harus memakai <code class="rounded bg-slate-100 px-1 text-xs">visibleTo()</code> pada
                daftar dan pada setiap <code class="rounded bg-slate-100 px-1 text-xs">findOrFail</code>.</li>
            <li>Catat setiap penyimpangan dari Core di <code
                    class="rounded bg-slate-100 px-1 text-xs">DECISIONS.md</code> aplikasi turunan.</li>
        </ul>
    </x-showcase.section>

    {{-- Rilis --}}
    <x-showcase.section id="rilis" title="Sebelum rilis"
        intro="Hal yang harus diganti atau dibuang pada aplikasi turunan.">
        <ul class="space-y-2 text-sm leading-relaxed text-slate-700">
            @foreach (['Nama aplikasi dan logo (Settings → General, huruf awal di shell auth dan sidebar)', 'Ilustrasi di public/images (auth-*.svg), pakai aset berlisensi', 'Tautan Terms dan Privacy, serta teks di halaman auth', 'Halaman awal: tambahkan widget lewat resources/views/dashboard/extra.blade.php, jangan menyunting dashboard Core', 'Landing contoh, kartu promo (admin.promo.enabled), dan komponen yang tidak dipakai', 'SHOWCASE_ENABLED=false dan halaman ini dibiarkan tidak terdaftar', 'Mailer dan queue sungguhan (bukan log atau sync), APP_DEBUG=false, npm run build', 'SESSION_DRIVER=database dan SETTINGS_CACHE_ENABLED=true', 'Akun Super Admin awal dibuat, lalu INITIAL_ADMIN_PASSWORD dihapus dari .env', 'DECISIONS.md dan REQUIREMENTS.md diperbarui untuk aplikasi ini'] as $item)
                <li class="flex items-start gap-3">
                    <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-orange-500" aria-hidden="true"></span>
                    {{ $item }}
                </li>
            @endforeach
        </ul>
    </x-showcase.section>
</x-layouts::app>
