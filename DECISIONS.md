# Keputusan Core

Dokumen ini mencatat keputusan yang sudah diambil beserta alasannya. Perubahan keputusan harus diperbarui di sini **pada perubahan kode yang sama**. Keputusan lama tidak dihapus: tandai "digantikan oleh Dxx".

Status: **Disepakati** (sudah diputuskan) atau **Asumsi** (dipakai di kode, belum dikonfirmasi).

## Data

**D01. UUID sebagai primary key.** Disepakati.
Konteks: id berurutan mudah ditebak dan sulit digabung antar sistem.
Konsekuensi: migrasi tabel pivot Spatie (`model_has_roles`, `model_has_permissions`) harus memakai `uuid` pada `model_morph_key` sebelum `migrate` dijalankan; salah satu langkah yang mudah terlupa pada proyek turunan.

**D02. Penonaktifan akun memakai soft delete.** Disepakati.
Konsekuensi: username dan email tetap terpakai setelah akun dinonaktifkan. Tidak ada hapus permanen di UI; jalur hapus permanen (jika diwajibkan hukum) dibuat terpisah.

## Autentikasi

**D03. Fortify sebagai backend autentikasi; halaman pra-login adalah Blade biasa dengan form POST ke route Fortify.** Disepakati.
Alasan: throttle, regenerasi sesi, alur 2FA, dan redirect dikelola satu pihak. Menulis ulang di Livewire berarti dua jalur yang bisa saling bertentangan.
Konsekuensi: Livewire hanya dipakai setelah login (Account, Users, Roles, Settings).

**D04. Login dengan email atau username.** Disepakati.
Detail: `fortify.username` tetap `email` dan **harus berupa kolom nyata**, karena Fortify membaca `$user->{username}` saat konfirmasi password. Kolom form itu menerima email atau username; pencocokannya dilakukan di `Fortify::authenticateUsing`. Waktu respons disamakan untuk akun yang tidak dikenal.

**D05. Pendaftaran publik default tertutup** (setelan `registration_enabled`). Asumsi.
Akun dibuat admin; undangan berupa tautan set password (alur reset password).
Catatan: masa berlaku tautan bawaan (60 menit) terlalu pendek untuk undangan.

**D06. Verifikasi email lewat tautan bertanda tangan, bukan kode OTP.** Disepakati.
Akun yang dibuat admin atau seeder otomatis terverifikasi.

**D07. 2FA TOTP bawaan Fortify, dengan konfirmasi dan kode pemulihan.** Disepakati.
Tindakan sensitif butuh konfirmasi password (`auth.password_timeout`, disarankan 900 detik).
Belum ada: reset 2FA oleh admin bagi pengguna yang kehilangan perangkat dan kode pemulihan.

**D08. Permintaan reset password dijawab sama untuk email dikenal maupun tidak.** Disepakati.
Alasan: mencegah pencarian akun. Konsekuensi: pengguna yang salah ketik tidak mendapat umpan balik.

## Otorisasi

**D09. Spatie Permission; enum `UserRole` dan `Ability` hanya data awal.** Disepakati.
Role tambahan dibuat lewat UI. Tabel `roles` punya kolom `level`, `is_system`, `description`.

**D10. Satu role per akun di UI.** Disepakati. Pivot tetap many-to-many; `primaryRole()` mengambil level tertinggi.

**D11. Hierarki "hanya di bawah".** Disepakati.
- Super Admin boleh mengelola semua akun, termasuk Super Admin lain.
- Admin dan Operator hanya mengelola akun dengan level lebih rendah (tidak sama).
- Tidak ada yang mengubah, menonaktifkan, atau mengganti role akunnya sendiri lewat halaman Users; perubahan diri sendiri lewat halaman Account.
- Super Admin terakhir tidak boleh dihapus atau diturunkan.

**D12. Aturan delegasi.** Disepakati.
Role yang diberikan harus berlevel di bawah pemberi (kecuali Super Admin). Permission yang dibagikan harus dipegang pemberi. Role sistem tidak dapat diubah atau dihapus. Role yang masih dipakai tidak dapat dihapus.

**D13. Seeder membuat role sistem sekali dan tidak menimpa permission hasil edit UI** (kecuali Super Admin yang mengikuti kode). Disepakati.
Konsekuensi: `Ability` baru tidak otomatis sampai ke Admin atau Operator; berikan lewat UI atau migrasi data. Cache permission dibersihkan manual di seeder karena `WithoutModelEvents` mematikan event model.

**D14. `Gate::before` Super Admin dibatasi pada nama permission di `Ability`.** Disepakati.
Alasan: aturan di policy (mis. super admin terakhir) tidak ikut terlewati.
Konsekuensi: pengecekan baru wajib lewat `$user->can('nama.permission')`, bukan `hasPermissionTo()`.

**D15. Level awal: Super Admin 100, Admin 80, Operator 50, Regular User 10.** Asumsi.

## Aplikasi

**D16. Setelan aplikasi memakai spatie/laravel-settings** (hanya General dan Mail). Disepakati.
Kredensial SMTP dan rahasia tetap di `.env`. `app.timezone` tidak diubah dari UI (hanya zona tampilan). Penerapan setelan di `boot()` dijaga agar tidak gagal saat tabel belum ada.

**D17. UI: komponen Blade buatan sendiri, Tailwind v4, token warna `brand-*`.** Disepakati.
Navigasi memakai `wire:navigate` dengan bar progres bawaan Livewire.

**D18. Notifikasi.** Disepakati.
Toast (Alpine) untuk konfirmasi tindakan; alert inline untuk pesan yang harus tetap terlihat; toast error menetap sampai ditutup; pesan setelah redirect lewat flash session.

**D19. Halaman error satu komponen tanpa JavaScript.** Disepakati.
Pesan exception tidak ditampilkan, kecuali pesan 403 yang ditulis sendiri.

**D20. Promo sidebar: setelah ditutup, disembunyikan sampai login baru** (flag di sesi server). Disepakati.

## Cakupan dan proses

**D21. Units tidak termasuk Core; ditambahkan per aplikasi.** Disepakati.
Titik sambung tunggal: `User::scopeVisibleTo()` dan `User::isVisibleTo()`. Keduanya harus konsisten; daftar dan akses langsung per id sama-sama wajib dibatasi.

**D22. Pengujian dengan Pest.** Disepakati.
Matriks policy (aktor × target × aksi) harus lulus sebelum perubahan aturan digabung. Ubah tabelnya lebih dulu, baru kodenya.

## Pertanyaan terbuka

- **Q01.** Palet: kode memakai Tailwind murni dengan token `brand-*` (indigo, mengikuti referensi desain). Jika tema DaisyUI dengan primary merah masih dikehendaki, pilih salah satu; keduanya tidak boleh hidup bersamaan.
- **Q02.** Masa berlaku tautan undangan akun baru.
- **Q03.** Siapa yang boleh mereset 2FA pengguna lain, dan bagaimana dicatat.
- **Q04.** Pencatatan aktivitas (perubahan role, penonaktifan akun, perubahan setelan).
- **Q05.** Bentuk API READ-only untuk dinas, dan di mana batas antara Core dan proyek.
