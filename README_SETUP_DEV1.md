# Panduan Menjalankan SPJ Monitoring BPS

Aplikasi monitoring Surat Pertanggungjawaban (SPJ) BPS Kabupaten Batang Hari.
Laravel 10 + Blade + Tailwind, database MySQL.

Panduan ini untuk developer yang baru pertama kali menjalankan proyek ini di
komputernya sendiri, mulai dari `git pull` sampai backend berjalan.

---

## 1. Prasyarat

| Kebutuhan | Versi | Catatan |
|---|---|---|
| PHP | 8.1 atau lebih baru | wajib ada ekstensi `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, `gd` |
| Composer | 2.x | |
| Node.js + npm | 18 atau lebih baru | |
| MySQL / MariaDB | 5.7 atau lebih baru | |
| `pdo_sqlite` | — | hanya jika ingin menjalankan `php artisan test`, lihat bagian 6 |

Cek ekstensi PHP yang sudah terpasang:

```bash
php -m | grep -E "pdo_mysql|mbstring|xml|curl|zip|gd"
```

---

## 2. Data pegawai

Repository ini **privat**. Karena itu `database/seeders/data/pegawai.json` —
berisi nama, email, dan hash password 36 pegawai — ikut di dalam repo, sehingga
`php artisan db:seed` langsung berhasil tanpa perlu meminta berkas tambahan.

Perlakukan isinya sebagai data pribadi: jangan disalin keluar repo, jangan
ditempel ke chat atau issue, dan jangan pernah menjadikan repository ini publik
tanpa lebih dulu mengeluarkan berkas itu beserta riwayatnya.

Satu berkas yang tetap **tidak** ikut:

| Berkas | Keterangan |
|---|---|
| `data.sql` | Dump database SIMABAR, 2,8 MB, 23 tabel yang tidak dipakai aplikasi ini. Minta lewat japri hanya bila perlu membuat ulang `pegawai.json` dari data terbaru. |

Kalau `pegawai.json` sampai hilang, `php artisan db:seed` akan berhenti dengan
pesan `Berkas data pegawai tidak ditemukan` dan tabel `users` kosong sehingga
tidak bisa login. Tabel peran dan status tetap terisi, jadi cukup pulihkan
berkasnya (`git checkout -- database/seeders/data/pegawai.json`) lalu ulangi.

---

## 3. Dari pull sampai backend berjalan

```bash
# 1. Ambil kode terbaru
git clone https://github.com/gdepradd/spj_monitoring_BPS.git
cd spj_monitoring_BPS
# (jika repo sudah pernah di-clone, cukup: git pull)

# 2. Pasang dependensi
composer install
npm install

# 3. Siapkan berkas konfigurasi
cp .env.example .env
php artisan key:generate
```

Buat database MySQL kosong, lalu sesuaikan bagian ini di `.env`:

```env
DB_DATABASE=spj_monitoring
DB_USERNAME=root
DB_PASSWORD=
```

```sql
CREATE DATABASE spj_monitoring CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Lanjutkan:

```bash
# 4. Bangun struktur tabel
php artisan migrate

# 5. Isi data awal: peran, status, dan 36 akun pegawai
php artisan db:seed

# 6. Jalankan dua proses ini di dua terminal terpisah
npm run dev          # terminal 1 - Vite, biarkan tetap jalan
php artisan serve    # terminal 2 - backend Laravel
```

Buka <http://127.0.0.1:8000>.

`npm run dev` harus tetap berjalan selama pengembangan. Tanpa itu, halaman
tampil tanpa CSS. Untuk sekali build saja, pakai `npm run build`.

### Kalau menarik perubahan terbaru (`git pull`)

```bash
git pull
composer install        # bila composer.lock berubah
npm install             # bila package-lock.json berubah
php artisan migrate     # bila ada migrasi baru
php artisan config:clear && php artisan view:clear
```

---

## 4. Login pertama kali

Password setiap pegawai adalah password yang mereka pakai di aplikasi asal
(SIMABAR), dan **tersimpan dalam bentuk hash** — tidak ada yang bisa
membacanya, termasuk pemilik repo.

Untuk keperluan pengembangan di komputer sendiri, ganti password salah satu
akun secara lokal.

Lihat daftar akun beserta perannya dari database setelah seed:

```bash
php artisan tinker --execute="
foreach (App\Models\User::with('role')->orderBy('id_role')->get() as \$u) {
    echo str_pad(\$u->role->nama_role, 14) . \$u->email . PHP_EOL;
}
"
```

Ambil satu email dari daftar itu, lalu ganti passwordnya secara lokal:

```bash
php artisan tinker --execute="
App\Models\User::firstWhere('email','ISI_EMAIL_DI_SINI')
    ->update(['password' => Hash::make('rahasia-lokal')]);
echo 'password lokal diganti';
"
```

Masuk dengan email tersebut dan password `rahasia-lokal`.

Perubahan ini hanya berlaku di database lokal Anda. Jangan pernah melakukannya
di database produksi.

Sebaran akun: 2 admin, 3 verifikator (urutan 1-3), 1 bendahara, 1 PPK, 1 PPSPM,
dan 28 pegawai. Tujuh akun bertanda nonaktif akan ditolak saat login — itu
memang disengaja.

---

## 5. Peran dan alur

Pengajuan berjalan berurutan:

```
Pegawai → Verifikator 1 → Verifikator 2 → Verifikator 3
        → Bendahara (metode & SPP) → PPK → PPSPM
        → Bendahara (konfirmasi) → Selesai
```

Pendaftaran mandiri sengaja ditutup. Pembuatan akun hanya lewat menu Admin di
`/admin/users`.

---

## 6. Menjalankan test

```bash
php artisan test
```

Test memakai `RefreshDatabase`, yang menjalankan `migrate:fresh`. Karena itu
`phpunit.xml` mengarahkannya ke SQLite in-memory, **terpisah dari database
pengembangan**. Jangan mengomentari kembali dua baris `DB_CONNECTION` dan
`DB_DATABASE` di sana — tanpa keduanya, `php artisan test` akan menghapus
seluruh isi database `spj_monitoring`.

Test membutuhkan ekstensi `pdo_sqlite`:

```bash
sudo apt install php8.1-sqlite3      # Ubuntu/Debian
```

Tanpa ekstensi itu, test gagal dengan pesan `could not find driver`. Itu aman —
tidak ada data yang tersentuh.

---

## 7. Sebelum commit dan push

Periksa dulu apa yang akan ikut:

```bash
git status
```

**Tidak boleh ikut** (sudah diamankan lewat `.gitignore`):

```
.env
.env.testing
data.sql
```

Kalau salah satunya muncul di `git status`, hentikan dan periksa `.gitignore`
sebelum melanjutkan. Berkas `.env` memuat APP_KEY dan kredensial database yang
selalu berbeda tiap mesin, jadi tidak pernah ikut ter-commit meskipun
repository ini privat.

Sebelum push:

```bash
npm run build
php artisan route:list
```

Hindari menjalankan seluruh rangkaian test di database yang berisi data
sungguhan sebelum memastikan bagian 6 sudah benar.

---

## 8. Catatan teknis

- **Zona waktu** diatur ke `Asia/Jakarta` (WIB) di `config/app.php` dan
  `config/database.php`. Keduanya harus sejalan, karena sebagian tabel mengisi
  `created_at` dari sisi MySQL, bukan dari PHP.
- **Nomor HP** tidak lagi disimpan. Kolomnya sudah dihapus lewat migrasi
  `2026_08_26_100000_drop_no_hp_from_users_table`.
- **Logo** berada di `public/aset/logo-bps.webp` dan dipanggil lewat komponen
  `<x-application-logo />`. Komponen itu tidak menetapkan ukuran; tentukan
  tinggi atau lebarnya di tempat pemakaian, jangan keduanya sekaligus.
- **`PegawaiRealSeeder`** aman dijalankan berulang. Pencocokan memakai email,
  dan seeder tidak menghapus baris apa pun.
