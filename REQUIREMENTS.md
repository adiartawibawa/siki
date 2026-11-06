# Kebutuhan Core

Core adalah kerangka aplikasi yang dipakai ulang untuk banyak proyek. Semua kebutuhan di sini harus berlaku untuk **beragam jenis proyek** (company profile, GIS, database, blog atau CMS), bukan hanya satu jenis. Hal yang spesifik proyek (mis. Units) ditambahkan pada aplikasi turunan.

Kolom Status: **Ada** (sudah dibuat), **Belum** (diputuskan, belum dikerjakan), **Turunan** (tanggung jawab aplikasi turunan).

## Fungsional

### Autentikasi

| ID | Kebutuhan | Status |
|---|---|---|
| A1 | Login dengan email atau username, dibatasi laju percobaan | Ada |
| A2 | Registrasi publik yang bisa dinyalakan atau dimatikan lewat setelan; default mati | Ada |
| A3 | Lupa dan reset password tanpa membocorkan apakah email terdaftar | Ada |
| A4 | Verifikasi email lewat tautan | Ada |
| A5 | Konfirmasi password sebelum tindakan sensitif | Ada |
| A6 | 2FA (TOTP) dengan konfirmasi, kode pemulihan, dan halaman tantangan saat login | Ada |
| A7 | Reset 2FA oleh admin untuk pengguna yang kehilangan akses | Belum |

### Otorisasi dan pengguna

| ID | Kebutuhan | Status |
|---|---|---|
| O1 | Empat role awal: Super Admin, Admin, Operator, Regular User | Ada |
| O2 | Admin dapat membuat dan mengubah role lewat UI, dengan level dan permission | Ada |
| O3 | Hierarki "hanya di bawah"; Super Admin dapat semuanya | Ada |
| O4 | Pengguna hanya dapat memberi role dan permission yang tidak melebihi miliknya | Ada |
| O5 | Manajemen pengguna: cari, filter, tambah, ubah, nonaktifkan, pulihkan | Ada |
| O6 | Akun baru membuat password sendiri lewat tautan di email | Ada |
| O7 | Pembatasan data per unit organisasi | Turunan |

### Akun pengguna dan setelan

| ID | Kebutuhan | Status |
|---|---|---|
| S1 | Profil, ubah nama dan email (email butuh password), ganti password | Ada |
| S2 | Keamanan akun: 2FA, kode pemulihan, sesi browser aktif | Ada |
| S3 | Setelan aplikasi (identitas, wilayah, akses, surel pengirim) oleh yang berhak | Ada |
| S4 | Pencatatan aktivitas penting (role, penonaktifan, setelan) | Belum |

### Antarmuka dan operasional

| ID | Kebutuhan | Status |
|---|---|---|
| U1 | Layout admin: sidebar bersarang dari config, topbar, drawer di mobile | Ada |
| U2 | Notifikasi (toast dan alert inline) | Ada |
| U3 | Halaman error (401, 403, 404, 419, 429, 500, 503) | Ada |
| U4 | Konfigurasi deploy | Belum |
| U5 | API READ-only untuk integrasi data (disiapkan sederhana; detail per proyek) | Belum |

## Non-fungsional

- **Keamanan:** aksi Livewire selalu mengotorisasi ulang di sisi server; kredensial dan rahasia hanya di `.env`; tidak ada pesan exception ke pengguna akhir.
- **Aksesibilitas:** semua kontrol dapat dioperasikan dengan keyboard, fokus terlihat, status tidak hanya dibedakan oleh warna, gerak dihormati `prefers-reduced-motion`.
- **Ketahanan:** halaman error tidak bergantung pada sesi, database, atau JavaScript.
- **Dapat dipakai ulang:** perubahan aturan dicatat di `DECISIONS.md`; jalur ke fitur turunan (mis. Units) lewat titik sambung yang terdokumentasi, bukan menyunting banyak berkas.
- **Teruji:** aturan otorisasi punya tes berbasis tabel; seeder diuji lewat `DatabaseSeeder`.

## Di luar lingkup Core

Units dan multi-tenancy, login Google (OAuth), unggah foto profil, UI token API, dan pelaporan.

## Syarat selesai

1. `migrate:fresh --seed` berhasil pada basis data kosong.
2. Semua tes Pest lulus, termasuk matriks policy.
3. Alur auth diuji manual: daftar (jika dibuka), login, 2FA, lupa dan reset password.
4. `DECISIONS.md` mutakhir terhadap kode.
5. Halaman contoh (dashboard, landing) dihapus atau diganti.
