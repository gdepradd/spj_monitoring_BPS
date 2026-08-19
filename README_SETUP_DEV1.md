# Setup Scaffold Junior Developer 1 — SPJ Monitoring BPS

Folder ini adalah **overlay** untuk repository Laravel `spj_monitoring_BPS` yang sudah ada. Salin isi folder ini ke root project Laravel Anda setelah meng-install Laravel Breeze (Blade).

## 1. Urutan pemasangan

Jalankan dari root repository:

```bash
composer install
cp .env.example .env
php artisan key:generate

composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
```

Setelah Breeze selesai membuat file default, **copy seluruh isi scaffold ini ke root project dan izinkan replace/overwrite file yang namanya sama**.

> Penting: file `database/migrations/2014_10_12_000000_create_users_table.php` pada scaffold ini memang menggantikan migration `users` bawaan Laravel karena struktur user pada issue memakai `id_user`, `nama_lengkap`, `id_role`, `status_aktif`, dan `urutan_verifikator`.

## 2. Database MySQL

Atur `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spj_monitoring_bps
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `spj_monitoring_bps`, lalu jalankan:

```bash
php artisan migrate:fresh --seed
```

## 3. Compile frontend

```bash
npm run dev
```

Untuk menjalankan aplikasi:

```bash
php artisan serve
```

## 4. Akun dummy

Semua akun dummy menggunakan password:

```text
password123
```

| Role | Nama | Email | Urutan |
|---|---|---|---|
| Admin | Admin BPS | admin@bps.test | - |
| Pegawai | Pegawai Satu | pegawai1@bps.test | - |
| Pegawai | Pegawai Dua | pegawai2@bps.test | - |
| Verifikator | Ida | ida@bps.test | 1 |
| Verifikator | Latif | latif@bps.test | 2 |
| Verifikator | Lanna | lanna@bps.test | 3 |
| PPK | PPK BPS | ppk@bps.test | - |
| Bendahara | Bendahara BPS | bendahara@bps.test | - |
| PPSPM | PPSPM BPS | ppspm@bps.test | - |

Nama Ida, Latif, dan Lanna serta urutan verifikator mengikuti issue. Email, nomor HP, nama akun selain yang ditentukan issue, dan password di atas adalah data dummy implementasi untuk pengujian dan boleh diganti tim.

## 5. Public registration

`routes/auth.php` pada scaffold tidak menyediakan route register. Pembuatan user dilakukan dari role Admin melalui `/admin/users`.

## 6. Remember me

Skema `users` pada issue tidak mencantumkan `remember_token`. Karena itu `LoginRequest` pada scaffold selalu login dengan `remember = false`, dan halaman login tidak menampilkan checkbox Remember Me.

## 7. Status revisi

Sesuai keputusan default pada issue, ketika pegawai memperbaiki pengajuan berstatus `REVISI`, `PengajuanController::update()` mengubah status kembali menjadi `VERIFIKASI_1`.

## 8. Model milik Dev 2

Scaffold Dev 1 sengaja **tidak membuat** model `Verifikasi`, `Ppk`, `Bendahara`, dan `Ppspm`, karena issue membagi model/logic tersebut kepada Dev 2. Dev 1 tetap membuat seluruh migration tabelnya. Halaman detail Pegawai membaca timeline tabel Dev 2 secara read-only menggunakan Query Builder sehingga tidak mengunci struktur model Dev 2.

## 9. Redirect login

- pegawai → `/pegawai/dashboard`
- verifikator → `/verifikator/dashboard`
- ppk → `/ppk/dashboard`
- bendahara → `/bendahara/dashboard`
- ppspm → `/ppspm/dashboard`
- admin → `/admin/users`

Route modul Dev 2 akan 404 sampai Dev 2 menambahkan route-nya; redirect-nya sudah disiapkan sesuai kontrak issue.

## 10. Branch strategy

Disarankan mengikuti issue:

```text
main
└── develop
    ├── feature/foundation-database
    ├── feature/auth-role
    └── feature/pegawai-pengajuan
```

Jika repository masih memakai `master`, koordinasikan rename/default branch dengan tim sebelum membuat `develop`.

## 11. Pemeriksaan sebelum push

```bash
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
npm run build
```

Pastikan alur minimal berikut bekerja:

1. Login `pegawai1@bps.test`.
2. Buat pengajuan.
3. Pengajuan mendapat nomor `PGJ-YYYYMMDD-XXXX` dan status `DIAJUKAN`.
4. Daftar/detail hanya menampilkan pengajuan milik pegawai login.
5. User role lain mendapat 403 saat mencoba route `/pegawai/*`.
6. Jika status diubah Dev 2 menjadi `REVISI`, pegawai dapat edit dan submit ulang.
7. Setelah submit revisi, status kembali `VERIFIKASI_1`.
