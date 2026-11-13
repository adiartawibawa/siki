@php
    $toc = [
        'kesiapan' => 'Kesiapan',
        'mulai' => 'Memulai proyek',
        'peta' => 'Peta berkas',
        'menu' => 'Menu sidebar',
        'halaman' => 'Halaman baru',
        'otorisasi' => 'Otorisasi',
        'api' => 'API',
        'pencarian' => 'Pencarian global',
        'notifikasi' => 'Notifikasi',
        'setelan' => 'Setelan aplikasi',
        'komponen' => 'Komponen UI',
        'turunan' => 'Aplikasi turunan',
        'rilis' => 'Sebelum rilis',
    ];

    // Kelas pembantu agar markup di bawah tetap pendek.
    $c = 'rounded bg-slate-100 px-1 text-xs';
    $ul = 'list-disc space-y-1.5 pl-5 text-sm leading-relaxed text-slate-700';

    $abilities = array_map(fn($a) => $a->value, \App\Enums\Ability::cases());
    $roleNames = array_map(fn($r) => $r->value, \App\Enums\UserRole::cases());

    // Route inti yang dirujuk kode Core. Salah satu hilang = tautan atau tombol yang error.
    $coreRoutes = [
        'login',
        'logout',
        'password.request',
        'dashboard',
        'profile',
        'account.details',
        'account.security',
        'account.api',
        'settings.general',
        'settings.mail',
        'users.index',
        'roles.index',
        'search',
        'notifications.index',
        'notifications.read-all',
        'notifications.show',
    ];
    $missingCore = collect($coreRoutes)->reject(fn($r) => Route::has($r))->values();

    // Route di config/admin.php yang tidak terdaftar (item yang dimatikan 'when' tidak dihitung).
    $missingNav = collect(config('admin.nav'))
        ->pluck('items')
        ->flatten(1)
        ->flatMap(fn($i) => $i['children'] ?? [$i])
        ->reject(fn($i) => isset($i['when']) && !config($i['when']))
        ->pluck('route')
        ->filter()
        ->reject(fn($r) => Route::has($r))
        ->values();

    // Sumber pencarian di config yang bukan turunan SearchSource.
    $badSources = collect(config('admin.search', []))
        ->reject(fn($class) => is_subclass_of($class, \App\Search\SearchSource::class))
        ->values();

    $columnIsNotInt = fn(string $table, string $column) => !str_contains(
        strtolower(
            collect(\Illuminate\Support\Facades\Schema::getColumns($table))->firstWhere('name', $column)['type_name'] ??
                'int',
        ),
        'int',
    );

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
            fn() => $columnIsNotInt('model_has_roles', 'model_id'),
            'Ubah model_morph_key menjadi uuid di migrasi Spatie sebelum migrate',
        ],
        [
            'Token API memakai UUID (personal_access_tokens.tokenable_id)',
            fn() => $columnIsNotInt('personal_access_tokens', 'tokenable_id'),
            'Ganti morphs("tokenable") menjadi uuidMorphs("tokenable") di migrasi Sanctum sebelum migrate',
        ],
        [
            'Notifikasi memakai UUID (notifications.notifiable_id)',
            fn() => $columnIsNotInt('notifications', 'notifiable_id'),
            'Pakai uuidMorphs("notifiable") di migrasi notifications',
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
            'fortify.username adalah kolom nyata di users',
            fn() => \Illuminate\Support\Facades\Schema::hasColumn('users', (string) config('fortify.username')),
            'Fortify membaca $user->{username} saat konfirmasi password. Biarkan "email"; login dengan username ditangani authenticateUsing',
        ],
        ['Route inti terdaftar', fn() => $missingCore->isEmpty(), 'Belum terdaftar: ' . $missingCore->implode(', ')],
        [
            'Semua route di config/admin.php terdaftar',
            fn() => $missingNav->isEmpty(),
            'Item tanpa route disembunyikan otomatis, jadi salah ketik tidak menimbulkan error. Belum terdaftar: ' .
            $missingNav->implode(', '),
        ],
        [
            'Sumber pencarian di config valid',
            fn() => $badSources->isEmpty(),
            'Harus turunan App\Search\SearchSource. Bermasalah: ' . $badSources->implode(', '),
        ],
        [
            'Token API punya masa berlaku (sanctum.expiration)',
            fn() => config('sanctum.expiration') !== null,
            'Bawaan Sanctum: token tidak pernah kedaluwarsa. Set SANCTUM_EXPIRATION (menit)',
        ],
        [
            'Endpoint tulis API tidak aktif di production',
            fn() => !(app()->isProduction() && config('admin.api.writes')),
            'Set ADMIN_API_WRITES=false kecuali ada klien yang benar-benar butuh',
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

    // Potongan yang memuat tag PHP ditulis sebagai string di sini.
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
    
            // ... panggil Action, bukan menulis logika di komponen ...
    
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
            menampilkan galat. Keputusan dan alasannya ada di <code class="{{ $c }}">DECISIONS.md</code>,
            kebutuhan di <code class="{{ $c }}">REQUIREMENTS.md</code>.
            Halaman ini hanya terdaftar bila <code class="{{ $c }}">SHOWCASE_ENABLED</code> aktif (bawaan:
            lingkungan local).
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
        <ul class="{{ $ul }}">
            <li>Migrasi pihak ketiga yang memakai <code class="{{ $c }}">morphs()</code> (Spatie, Sanctum,
                notifikasi) harus diubah ke <code class="{{ $c }}">uuid</code> sebelum <code
                    class="{{ $c }}">migrate</code>. Pemeriksaan di atas memeriksanya satu per satu.</li>
            <li>Seeder membuat role sistem sekali dan tidak menimpa hasil edit di UI. <code
                    class="{{ $c }}">Ability</code> baru tidak otomatis sampai ke Admin atau Operator.</li>
            <li>Pendaftaran publik default tertutup. Akun dibuat admin dan memilih password sendiri lewat tautan di
                email.</li>
            <li>Token API tidak punya masa berlaku bawaan: set <code
                    class="{{ $c }}">SANCTUM_EXPIRATION</code>. Endpoint tulis API mati sampai <code
                    class="{{ $c }}">ADMIN_API_WRITES=true</code>.</li>
            <li>Pembersihan notifikasi berjalan lewat penjadwal. Pastikan cron memanggil <code
                    class="{{ $c }}">php artisan schedule:run</code> tiap menit.</li>
        </ul>
    </x-showcase.section>

    {{-- Peta berkas --}}
    <x-showcase.section id="peta" title="Peta berkas" intro="Di mana mencari apa.">
        <x-showcase.code lang="text">
            @verbatim
                app/
                ├─ Actions/
                │ ├─ Api/ CreateApiToken, RevokeApiTokens
                │ ├─ Fortify/ CreateNewUser, ResetUserPassword
                │ ├─ Roles/ SaveRole, DeleteRole
                │ └─ Users/ ChangeRole
                ├─ Enums/ UserRole (role awal), Ability (permission), ApiScope (scope token)
                ├─ Http/
                │ ├─ Controllers/ SearchController, NotificationController, Api/*
                │ ├─ Middleware/ EnsureApiAccess
                │ ├─ Requests/Roles/ RoleRules, StoreRoleRequest, UpdateRoleRequest
                │ └─ Resources/ RoleResource, PermissionResource, UserResource
                ├─ Livewire/Concerns/ Notifies (toast dari komponen)
                ├─ Models/ User (level, visibleTo, changePassword), Role (level, is_system)
                ├─ Notifications/ AccountNotice
                ├─ Policies/ UserPolicy, RolePolicy ← aturan hierarki di sini
                ├─ Providers/ FortifyServiceProvider, AppServiceProvider
                ├─ Search/ SearchSource, UserSearch, RoleSearch
                ├─ Settings/ GeneralSettings, MailSettings
                └─ Support/ Navigation (menu), GlobalSearch (pencarian)
                config/admin.php menu, search, api, promo, akun awal, showcase
                routes/ admin.php (halaman), api.php (API v1), console.php (jadwal)
                resources/views/
                ├─ layouts/ base, guest, app
                ├─ components/
                │ ├─ admin/ sidebar, nav-item, topbar, search, notifications, promo, save-bar
                │ ├─ account/tabs
                │ ├─ auth/shell
                │ ├─ form/ input, select, toggle
                │ ├─ ui/ button, card, badge, alert, dropdown, modal, search, ...
                │ ├─ showcase/ khusus halaman ini
                │ └─ errors/page
                ├─ auth/ halaman Fortify (Blade biasa, form POST)
                ├─ errors/ 401 … 503
                └─ pages/admin/ dashboard, account, users, roles, settings (Livewire)
                database/seeders/RolePermissionSeeder.php
                tests/Feature/ policy, akses, auth, navigasi, API, pencarian, notifikasi
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
        <ul class="{{ $ul }}">
            <li><code class="{{ $c }}">icon</code> adalah nama berkas di <code
                    class="{{ $c }}">resources/svg/lucide</code>. Nama yang salah tidak menimbulkan galat,
                hanya komentar HTML.</li>
            <li>Salah ketik nama route membuat item hilang tanpa pesan. Pemeriksaan "Semua route terdaftar" di atas ada
                untuk itu.</li>
            <li>Menu hanya mengatur tampilan. Otorisasi sebenarnya ada di route dan di dalam aksi komponen.</li>
        </ul>
    </x-showcase.section>

    {{-- Halaman baru --}}
    <x-showcase.section id="halaman" title="Menambah halaman"
        intro="Pola yang dipakai Users dan Roles: route yang dijaga policy, komponen Livewire yang tipis, logika tulis di Action, otorisasi ulang di setiap aksi.">
        <x-showcase.code lang="php">
            @verbatim
                // routes/admin.php: halaman biasa, di dalam grup ['auth', 'verified']
                Route::livewire('/contoh', 'pages::admin.contoh.index')->name('contoh.index');

                // halaman yang dijaga policy: grup sendiri, dan jangan lupa 'verified'
                Route::middleware(['auth', 'verified', 'can:viewAny,' . \App\Models\Contoh::class])->group(function () {
                Route::livewire('/contoh', 'pages::admin.contoh.index')->name('contoh.index');
                });
            @endverbatim
        </x-showcase.code>
        <x-showcase.code
            lang="resources/views/pages/admin/contoh/⚡index.blade.php">{!! $sfc !!}</x-showcase.code>
        <ul class="{{ $ul }}">
            <li>Rujuk route dengan nama yang benar-benar terdaftar, mis. <code
                    class="{{ $c }}">route('account.details')</code>, bukan <code
                    class="{{ $c }}">route('account')</code>. Nama yang tidak ada baru terasa saat halaman
                dirender.</li>
            <li>Tautan internal diberi <code class="{{ $c }}">wire:navigate</code> agar ada bar progres.
                Form biasa (logout) memuat ulang halaman penuh.</li>
            <li>Setelah aksi yang berakhir redirect, kirim pesan lewat <code
                    class="{{ $c }}">session()->flash('toast', …)</code>, bukan <code
                    class="{{ $c }}">notify()</code>.</li>
            <li>Logika tulis (simpan, hapus, ubah role) berada di Action di <code
                    class="{{ $c }}">app/Actions</code>, dipanggil oleh Livewire maupun API.</li>
        </ul>
    </x-showcase.section>

    {{-- Otorisasi --}}
    <x-showcase.section id="otorisasi" title="Otorisasi"
        intro="Spatie Permission dengan role di database. Enum UserRole dan Ability hanya data awal; role tambahan dibuat lewat halaman Roles.">
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
        <ul class="{{ $ul }}">
            <li>Aturannya "hanya di bawah": level yang sama tidak boleh saling mengelola. Tidak ada yang mengubah
                akunnya sendiri lewat halaman Users; itu lewat Account.</li>
            <li>Role yang diberikan harus berlevel di bawah pemberinya, dan permission yang dibagikan harus dipegang
                pemberinya. Penjaganya ada di <code class="{{ $c }}">SaveRole</code> dan <code
                    class="{{ $c }}">ChangeRole</code>, bukan di komponen.</li>
            <li>Semua penulisan role lewat <code class="{{ $c }}">SaveRole</code>, <code
                    class="{{ $c }}">DeleteRole</code>, dan <code
                    class="{{ $c }}">ChangeRole</code>. Satu <code
                    class="{{ $c }}">Role::create()</code> di tempat lain membuka celah eskalasi hak.</li>
            <li>Gunakan <code class="{{ $c }}">$user->can('nama.permission')</code>, bukan <code
                    class="{{ $c }}">hasPermissionTo()</code>: yang kedua tidak melewati Gate, sehingga
                Super Admin ikut ditolak.</li>
            <li>Dua permission API: <code class="{{ $c }}">api.access</code> (boleh memakai API dengan token
                pribadi) dan <code class="{{ $c }}">api.tokens.manage</code> (mencabut token akun di
                bawahnya).</li>
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

    {{-- API --}}
    <x-showcase.section id="api" title="API"
        intro="API v1 hanya baca secara bawaan. Setiap permintaan harus lolos empat lapis; yang bocor di satu lapis tidak cukup untuk masuk.">
        <ol class="list-decimal space-y-1.5 pl-5 text-sm leading-relaxed text-slate-700">
            <li><strong>Token sah</strong> (Sanctum): belum kedaluwarsa, pemiliknya belum dinonaktifkan.</li>
            <li><strong>Pemilik masih memegang <code class="{{ $c }}">api.access</code></strong> lewat
                rolenya (middleware <code class="{{ $c }}">api.access</code>). Mencabutnya dari role
                mematikan semua token pemegangnya seketika, tanpa menghapusnya.</li>
            <li><strong>Token punya scope</strong> endpoint itu (<code
                    class="{{ $c }}">abilities:users:read</code>). Scope yang boleh dipilih dibatasi
                permission pemiliknya (<code class="{{ $c }}">ApiScope::requires()</code>).</li>
            <li><strong>Policy mengizinkan</strong>, sama seperti di UI (hierarki, <code
                    class="{{ $c }}">visibleTo</code>).</li>
        </ol>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[32rem] text-left text-sm text-slate-700">
                <thead class="text-slate-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Scope</th>
                        <th class="py-2 pr-4 font-medium">Memerlukan permission</th>
                        <th class="py-2 font-medium">Endpoint</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-2 pr-4"><code class="{{ $c }}">users:read</code></td>
                        <td class="pr-4">users.view</td>
                        <td>GET /api/v1/users, /users/{user}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4"><code class="{{ $c }}">roles:read</code></td>
                        <td class="pr-4">roles.view</td>
                        <td>GET /api/v1/roles, /roles/{role}, /permissions</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4"><code class="{{ $c }}">roles:write</code></td>
                        <td class="pr-4">roles.manage</td>
                        <td>POST, PUT, DELETE /api/v1/roles (hanya bila ADMIN_API_WRITES=true)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <x-showcase.code lang="langkah menambah endpoint baca">
            @verbatim
                1. Resource : app/Http/Resources/LaporanResource.php (@mixin App\Models\Laporan)
                2. Controller : app/Http/Controllers/Api/LaporanController.php (#[Group('Laporan')],
                Gate::authorize('viewAny', ...), query ->visibleTo($request->user()))
                3. Scope : tambah case di App\Enums\ApiScope + requires() ke permission-nya
                4. Route : routes/api.php → ->middleware('abilities:laporan:read')
                5. Tes : token tanpa scope = 403, tanpa api.access = 403, bentuk JSON sesuai Resource
                6. Dokumen : buka /docs/api dan periksa hasilnya
            @endverbatim
        </x-showcase.code>

        <ul class="{{ $ul }}">
            <li>Token dibuat pemiliknya di halaman <code class="{{ $c }}">Account → API</code>: nama,
                scope, masa berlaku 30, 90, atau 180 hari, dan konfirmasi password. Token ditampilkan sekali; yang
                tersimpan hanya hash.</li>
            <li>Token dicabut otomatis saat akun dinonaktifkan, saat password direset, dan saat role pemiliknya
                kehilangan <code class="{{ $c }}">api.access</code> lewat halaman Users.</li>
            <li>Untuk integrasi antar-sistem, pakai akun layanan dengan role khusus, bukan akun pegawai: token pribadi
                ikut mati ketika orangnya pindah.</li>
            <li>Dokumentasi otomatis (Scramble) ada di <code class="{{ $c }}">/docs/api</code>. Di luar
                local hanya Super Admin yang bisa membukanya; untuk dibagikan ke pihak lain gunakan <code
                    class="{{ $c }}">php artisan scramble:export</code>.</li>
            <li>Permintaan selalu dibalas JSON untuk <code class="{{ $c }}">api/*</code>, dibatasi 60 per
                menit per pengguna.</li>
        </ul>
    </x-showcase.section>

    {{-- Pencarian global --}}
    <x-showcase.section id="pencarian" title="Pencarian global"
        intro="Kotak pencarian di topbar (⌘K atau Ctrl K). Mengikuti pola global search Filament: tiap model punya satu kelas SearchSource yang mendeklarasikan cara ia dicari; mesinnya generik.">
        <x-showcase.code lang="php">
            @verbatim
                // app/Search/SchoolSearch.php (turunan App\Search\SearchSource)
                public function label(): string { return 'Schools'; }
                public function attributes(): array { return ['name', 'npsn', 'district.name']; } // titik = relasi
                public function canSearch(User $actor): bool { return $actor->can('viewAny', School::class); }
                public function query(User $actor): Builder { return School::query()->visibleTo($actor)->with('district'); }
                public function title(Model $record): string { return $record->name; }
                public function details(Model $record): array { return ['NPSN' => $record->npsn]; }
                public function url(Model $record): string { return route('schools.show', $record); }

                // config/admin.php → 'search' => [ ..., \App\Search\SchoolSearch::class ]
            @endverbatim
        </x-showcase.code>
        <ul class="{{ $ul }}">
            <li>Hanya sumber yang terdaftar di <code class="{{ $c }}">admin.search</code> yang dicari.
                Daftar kosong menyembunyikan kotak pencarian.</li>
            <li>Kata kunci dipecah per kata: tiap kata harus cocok di setidaknya satu kolom. Minimal 2 karakter,
                maksimal 5 kata, 5 hasil per kelompok. <code class="{{ $c }}">%</code> dan <code
                    class="{{ $c }}">_</code> dianggap huruf biasa.</li>
            <li><strong>Keamanan ada di sumber.</strong> Mesin hanya memanggil <code
                    class="{{ $c }}">canSearch</code> dan <code class="{{ $c }}">query</code>.
                Sumber yang lupa <code class="{{ $c }}">visibleTo</code> atau policy membocorkan data yang
                disembunyikan di halaman biasa.</li>
            <li>Urutan kelompok mengikuti <code class="{{ $c }}">sort()</code>, bukan urutan di config.
                Akun yang dinonaktifkan tidak pernah muncul.</li>
        </ul>
    </x-showcase.section>

    {{-- Notifikasi --}}
    <x-showcase.section id="notifikasi" title="Notifikasi"
        intro="Dua jenis, untuk dua keperluan: toast dan alert untuk umpan balik tindakan, dan lonceng di topbar untuk riwayat pemberitahuan akun.">

        <h3 class="text-sm font-semibold text-slate-900">Toast dan alert</h3>
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

        <h3 class="pt-2 text-sm font-semibold text-slate-900">Lonceng (notifikasi akun)</h3>
        <p class="max-w-[70ch] text-sm leading-relaxed text-slate-700">
            Tersimpan di tabel <code class="{{ $c }}">notifications</code> dan hanya ditujukan kepada
            pemilik akun.
            Empat peristiwa bawaan: role diubah orang lain, token API dicabut admin, password diganti, dan 2FA yang
            sudah aktif dimatikan.
        </p>
        <x-showcase.code lang="php">
            @verbatim
                // Satu kelas, dua argumen: teks pendek dan tautan (path relatif, bukan URL penuh)
                $user->notify(new AccountNotice('Pesan pendek.', route('account.security', absolute: false)));

                // Rujuk route yang pasti ada. Pemicu berada di dalam tindakan utama (sering di dalam transaksi),
                // jadi nama route yang salah akan menggagalkan tindakan itu, bukan hanya notifikasinya.
            @endverbatim
        </x-showcase.code>
        <ul class="{{ $ul }}">
            <li>Isinya hanya teks dan tautan. Jangan memuat token, password, atau nama orang yang melakukan perubahan
                ("an administrator").</li>
            <li>Mengganti password selalu lewat <code class="{{ $c }}">User::changePassword()</code>, bukan
                <code class="{{ $c }}">forceFill</code> langsung, agar pemiliknya diberi tahu.</li>
            <li>Tidak ada polling atau real-time: hitungan dimuat saat halaman dimuat, daftar saat lonceng dibuka.
                Notifikasi keamanan sebaiknya juga dikirim lewat email bila SMTP tersedia, karena korban pembajakan akun
                tidak melihat lonceng.</li>
            <li>Pembersihan: <code class="{{ $c }}">php artisan notifications:prune</code> (terjadwal
                harian) menghapus yang dibaca lebih dari 30 hari dan yang tak pernah dibaca lebih dari 180 hari.</li>
        </ul>
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

                // 3. resources/views/pages/admin/settings/⚡general.blade.php
                // tambah properti di komponen, field di form, dan aturan validasi di save()

                // Membaca di mana saja
                app(\App\Settings\GeneralSettings::class)->footer_text;
            @endverbatim
        </x-showcase.code>
        <ul class="{{ $ul }}">
            <li>Properti tanpa migrasi nilai awal akan melempar <code
                    class="{{ $c }}">MissingSettings</code>.</li>
            <li>Zona waktu di Settings hanya zona <em>tampilan</em> (mis. sapaan di dashboard). Penyimpanan tetap UTC.
            </li>
            <li>Di production, aktifkan cache setelan (<code
                    class="{{ $c }}">SETTINGS_CACHE_ENABLED</code>). Dengan cache aktif, perubahan langsung
                di database butuh <code class="{{ $c }}">php artisan settings:clear-cache</code>.</li>
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
                    <x-ui.button size="icon-sm" variant="ghost" aria-label="Opsi"><x-ui.icon name="ellipsis"
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

        <x-showcase.example title="Kolom pencarian (ui.search)">
            @verbatim
                <div class="max-w-sm space-y-3">
                    <x-ui.search outline placeholder="Cari akun…" />
                    <x-ui.search placeholder="Varian bulat" />
                </div>
            @endverbatim
        </x-showcase.example>

        <x-showcase.example title="Bagian formulir (ui.form-section)">
            @verbatim
                <x-ui.form-section title="Informasi pribadi"
                    description="Judul dan penjelasan di kiri, kartu isian di kanan.">
                    <x-form.input label="Nama" name="demo_fs" placeholder="Nama lengkap" />

                    <x-slot:actions>
                        <x-ui.button type="submit" variant="dark">Simpan</x-ui.button>
                    </x-slot:actions>
                </x-ui.form-section>
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

    {{-- Turunan --}}
    <x-showcase.section id="turunan" title="Aplikasi turunan"
        intro="Core sengaja tidak punya Units atau pembatasan data per organisasi. Itu ditambahkan per aplikasi lewat satu titik sambung di model User, yang dipakai halaman Users, dashboard, pencarian, dan API.">
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
        <ul class="{{ $ul }}">
            <li>Scope menyaring <strong>daftar</strong>, <code class="{{ $c }}">isVisibleTo</code>
                menyaring <strong>akses langsung per id</strong> lewat policy. Lupa salah satunya adalah kebocoran data.
            </li>
            <li>Setiap permukaan baru yang menampilkan akun harus memakainya: halaman, <strong>sumber
                    pencarian</strong>, dan <strong>endpoint API</strong>. Pada ketiganya <code
                    class="{{ $c }}">visibleTo()</code> ditaruh di query dasar.</li>
            <li>Widget dashboard aplikasi ditaruh di <code
                    class="{{ $c }}">resources/views/dashboard/extra.blade.php</code>; Core menyertakannya
                otomatis, tanpa menyunting dashboard Core.</li>
            <li>Catat setiap penyimpangan dari Core di <code class="{{ $c }}">DECISIONS.md</code> aplikasi
                turunan.</li>
        </ul>
    </x-showcase.section>

    {{-- Rilis --}}
    <x-showcase.section id="rilis" title="Sebelum rilis"
        intro="Hal yang harus diganti atau dibuang pada aplikasi turunan.">
        <ul class="space-y-2 text-sm leading-relaxed text-slate-700">
            @foreach ([
        'Nama aplikasi dan logo (Settings → General, huruf awal di shell auth dan sidebar)',
        'Ilustrasi di public/images (auth-*.svg), pakai aset berlisensi, dan font dihosting sendiri bila jaringan membatasi akses keluar',
        'Tautan Terms dan Privacy, serta teks di halaman auth dan landing',
        'Halaman awal: tambahkan widget lewat resources/views/dashboard/extra.blade.php, jangan menyunting dashboard Core',
        'Kartu promo (admin.promo.enabled) dan komponen yang tidak dipakai',
        'SHOWCASE_ENABLED=false dan halaman ini dibiarkan tidak terdaftar',
        'Mailer dan queue sungguhan (bukan log atau sync), APP_DEBUG=false, npm run build',
        'SESSION_DRIVER=database dan SETTINGS_CACHE_ENABLED=true',
        'SANCTUM_EXPIRATION diisi; ADMIN_API_WRITES=false kecuali ada klien yang butuh; akses /docs/api dibatasi atau diganti berkas hasil scramble:export',
        'Cron untuk php artisan schedule:run agar notifikasi lama dibersihkan',
        'Akun Super Admin awal dibuat, lalu INITIAL_ADMIN_PASSWORD dihapus dari .env',
        'Pemeriksaan kesiapan di halaman ini semuanya OK, dan seluruh tes lulus',
    ] as $item)
                <li class="flex items-start gap-3">
                    <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-orange-500" aria-hidden="true"></span>
                    {{ $item }}
                </li>
            @endforeach
        </ul>
    </x-showcase.section>
</x-layouts::app>
