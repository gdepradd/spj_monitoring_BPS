# ISSUE — Junior Developer 1
## Modul: Foundation, Auth & Manajemen Data Master, POV 1 (Pengajuan – Pegawai)

> Dokumen ini adalah pasangan dari `issue-junior-dev-2-verifikasi-pencairan.md`.
> Kedua dokumen WAJIB dibaca bersama sebelum mulai coding, karena banyak bagian yang saling terhubung (skema database, kode status, warna, penamaan route).

---

## 1. Ringkasan Proyek

Web app **monitoring pengajuan pembayaran** kantor, dibangun dengan **Laravel + MySQL**. Tidak ada upload berkas — pegawai hanya mengisi form data pengajuan.

Ada 3 POV (sudut pandang user) sesuai Use Case Diagram:

| POV | Aktor | Tanggung jawab di project ini |
|---|---|---|
| POV 1 — Pengajuan | Pegawai | **Kamu (Dev 1)** |
| POV 2 — Verifikasi | Verifikator 1, 2, 3 | Dev 2 |
| POV 3 — Pencairan | PPK, Bendahara, PPSPM | Dev 2 |

Kamu bertanggung jawab atas **fondasi aplikasi** (setup project, auth, role, layout dasar, seluruh tabel master/lookup) dan **modul Pegawai** (POV 1) secara penuh. Dev 2 akan membangun di atas fondasi yang kamu buat, jadi bagian ini harus selesai dan stabil lebih dulu (lihat urutan pengerjaan di bagian 7).

---

## 2. Tech Stack

- **Backend:** Laravel (versi terbaru LTS yang tersedia saat project dimulai)
- **Database:** MySQL
- **Frontend:** Blade + Tailwind CSS (gunakan Laravel Breeze sebagai starter kit auth, opsi *Blade*, bukan Livewire/Inertia — supaya keduanya konsisten dan gampang di-review)
- **JS ringan (opsional):** Alpine.js (sudah include di Breeze) untuk interaksi kecil (dropdown, modal, toast)
- **Autorisasi:** Laravel Gate/Policy + Middleware custom berbasis `role` di tabel `roles`

---

## 3. Design System (WAJIB SAMA dengan Dev 2)

Supaya tampilan antar POV konsisten, gunakan token warna berikut. Simpan sebagai custom color di `tailwind.config.js` agar bisa dipakai `bg-pov-pengajuan`, `bg-status-acc`, dst.

### 3.1 Warna per POV (sidebar, badge modul, aksen tombol utama)

| POV | Nama token | Hex |
|---|---|---|
| POV 1 — Pengajuan (Pegawai) | `pov-pengajuan` | `#2563EB` (biru) |
| POV 2 — Verifikasi | `pov-verifikasi` | `#16A34A` (hijau) |
| POV 3 — Pencairan | `pov-pencairan` | `#7C3AED` (ungu) |

### 3.2 Warna status (dipakai di badge, di semua tabel status: `status_pengajuan`, `status_verifikasi`, `status_pencairan`)

| Makna status | Token | Hex |
|---|---|---|
| Menunggu / Diproses | `status-pending` | `#F59E0B` (amber) |
| ACC / Sesuai / Disetujui | `status-approved` | `#16A34A` (hijau) |
| Revisi | `status-revisi` | `#F97316` (oranye) |
| Ditolak | `status-rejected` | `#DC2626` (merah) |
| Selesai / Dicairkan | `status-done` | `#0D9488` (teal) |
| Netral / Draft | `status-neutral` | `#64748B` (abu-abu) |

### 3.3 Warna netral UI

- Background halaman: `#F8FAFC`
- Card/panel: `#FFFFFF`, border `#E2E8F0`
- Teks utama: `#1E293B`, teks sekunder: `#64748B`

**Buat komponen Blade reusable** (bagian ini kamu yang buat, dipakai juga oleh Dev 2):
- `<x-status-badge :kode="$kode_status" :label="$nama_status" />` → otomatis pilih warna dari mapping kode status di atas.
- `<x-app-layout>` dengan slot sidebar berbeda warna sesuai role yang login (pakai token POV di atas).
- `<x-timeline-item />` untuk menampilkan riwayat status (dipakai di halaman "Riwayat Pengajuan" milikmu, dan dikonsumsi ulang oleh Dev 2 di dashboard verifikator/pencairan).

---

## 4. Skema Database (Referensi Lengkap — WAJIB SAMA PERSIS dengan dokumen Dev 2)

Ini skema final berdasarkan ERD. Kamu yang membuat **semua migration**, seluruh tabel di bawah, supaya urutan foreign key konsisten dan tidak bentrok dengan migration Dev 2. Setelah migration kamu selesai dan di-push, Dev 2 baru boleh mulai (lihat bagian 7).

### 4.1 Urutan migration

1. `roles`
2. `users`
3. `status_pengajuan`
4. `status_verifikasi`
5. `status_pencairan`
6. `pengajuan`
7. `verifikasi`
8. `ppk`
9. `bendahara`
10. `ppspm`

### 4.2 Detail kolom

**roles**
- `id_role` PK
- `nama_role` (enum/string: `pegawai`, `verifikator`, `ppk`, `bendahara`, `ppspm`, `admin`)
- `keterangan`

**users**
- `id_user` PK
- `nama_lengkap`
- `email` UNIQUE
- `password`
- `no_hp`
- `id_role` FK → roles
- `status_aktif` (boolean)
- `urutan_verifikator` (integer, **nullable** — kolom TAMBAHAN di luar ERD asli, khusus untuk user dengan role `verifikator`, isi 1/2/3 untuk menentukan dia bertugas di tahap berapa. Lihat catatan di bagian 8.)
- `created_at`, `updated_at`

**status_pengajuan**
- `id_status` PK
- `kode_status` UNIQUE — pakai kode berikut (urutan sesuai kolom `urutan`):
  1. `DIAJUKAN`
  2. `VERIFIKASI_1`
  3. `VERIFIKASI_2`
  4. `VERIFIKASI_3`
  5. `REVISI`
  6. `DITOLAK`
  7. `PROSES_PPK`
  8. `PROSES_BENDAHARA`
  9. `PROSES_PPSPM`
  10. `SELESAI`
- `nama_status`, `keterangan`, `urutan` (int)

**status_verifikasi**
- `id_status_verifikasi` PK
- `kode_status` UNIQUE: `MENUNGGU`, `ACC`, `REVISI`, `TOLAK`
- `nama_status`, `keterangan`

**status_pencairan**
- `id_status_pencairan` PK
- `kode_status` UNIQUE: `MENUNGGU`, `SESUAI`, `TIDAK_SESUAI`, `SELESAI`
- `nama_status`, `keterangan`

**pengajuan**
- `id_pengajuan` PK
- `no_pengajuan` UNIQUE (format saran: `PGJ-YYYYMMDD-XXXX`, auto generate)
- `id_user` FK → users (pemohon)
- `tanggal_pengajuan`
- `perihal`
- `keterangan`
- `total_nominal` (decimal 15,2)
- `id_status` FK → status_pengajuan
- `catatan_pengaju` (nullable — dipakai saat pegawai revisi ulang)
- `created_at`, `updated_at`

**verifikasi** *(dibuat migration-nya oleh kamu, tapi fitur/logic-nya dikerjakan Dev 2)*
- `id_verifikasi` PK
- `id_pengajuan` FK → pengajuan
- `id_verifikator` FK → users
- `tahap` (tinyint: 1, 2, atau 3)
- `tanggal_verifikasi`
- `id_status_verifikasi` FK → status_verifikasi
- `catatan`
- `created_at`

**ppk / bendahara / ppspm** *(struktur identik, migration oleh kamu, fitur oleh Dev 2)*
- `id_ppk` / `id_bendahara` / `id_ppspm` PK
- `id_pengajuan` FK → pengajuan
- `tanggal_proses`
- `id_status` FK → status_pencairan
- `catatan`
- `created_at`

### 4.3 Seeder yang harus kamu buat

- `RoleSeeder` → isi 6 role di atas
- `StatusPengajuanSeeder`, `StatusVerifikasiSeeder`, `StatusPencairanSeeder` → isi sesuai kode di 4.2
- `UserSeeder` → buat akun dummy: 1 admin, beberapa pegawai, **Verifikator 1 (Ida, urutan_verifikator=1)**, **Verifikator 2 (Latif, urutan_verifikator=2)**, **Verifikator 3 (Lanna, urutan_verifikator=3)**, 1 PPK, 1 Bendahara, 1 PPSPM — sesuai nama di Use Case Diagram supaya enak dites bersama.

---

## 5. Scope Pekerjaan Kamu (Checklist)

### Fase 0 — Setup Project (prioritas pertama, blocking untuk Dev 2)
- [ ] Install Laravel + konfigurasi `.env` (DB MySQL)
- [ ] Install Laravel Breeze (Blade)
- [ ] Setup Tailwind config dengan token warna di bagian 3
- [ ] Buat repo Git, branch strategy: `main` → `develop` → feature branch (`feature/nama-fitur`)
- [ ] Buat semua migration (bagian 4.1–4.2)
- [ ] Buat semua seeder (bagian 4.3), pastikan `php artisan migrate:fresh --seed` jalan tanpa error
- [ ] Push ke `develop`, kabari Dev 2 bahwa schema sudah siap

### Fase 1 — Auth & Role
- [ ] Modifikasi login Breeze: setelah login, redirect sesuai `nama_role` user (pegawai → `/pegawai/dashboard`, verifikator → `/verifikator/dashboard`, ppk → `/ppk/dashboard`, dst.)
- [ ] Buat middleware `role:pegawai`, `role:verifikator`, `role:ppk`, `role:bendahara`, `role:ppspm`, `role:admin`
- [ ] Registrasi hanya untuk admin (buat user baru), bukan self-register publik — sesuaikan Breeze (nonaktifkan halaman register publik atau proteksi dengan middleware admin)
- [ ] Halaman "403 Unauthorized" custom jika role salah akses route

### Fase 2 — Layout Dasar & Komponen Reusable
- [ ] `<x-app-layout>` dengan sidebar dinamis per role (warna sesuai token POV)
- [ ] `<x-status-badge>` komponen (bagian 3.3)
- [ ] `<x-timeline-item>` komponen riwayat status
- [ ] Komponen form standar (`<x-input-label>`, `<x-text-input>`, `<x-primary-button>` — sudah ada di Breeze, sesuaikan warna)
- [ ] Halaman error umum (404, 500) mengikuti layout

### Fase 3 — Modul Pegawai (POV 1) — CRUD Pengajuan
Route prefix: `/pegawai/*`, middleware `role:pegawai`

- [ ] `GET /pegawai/dashboard` — ringkasan jumlah pengajuan per status (kartu statistik)
- [ ] `GET /pegawai/pengajuan` — **Lihat Daftar Pengajuan** (list semua pengajuan milik user login, dengan badge status, filter by status, pagination)
- [ ] `GET /pegawai/pengajuan/create` & `POST /pegawai/pengajuan` — **Ajukan Pengajuan Pembayaran**
  - Form: perihal, keterangan, total_nominal, tanggal_pengajuan
  - Saat submit: generate `no_pengajuan` otomatis, set `id_status` = `DIAJUKAN`
  - (Catatan: use case menyebut "Upload Dokumen SPJ Pembayaran" sebagai `<<include>>`, tapi requirement dari kantor: **tidak ada upload berkas**. Cukup field `keterangan` yang menampung ringkasan dokumen SPJ secara teks. Tulis catatan ini juga di halaman form supaya jelas bagi user.)
- [ ] `GET /pegawai/pengajuan/{id}` — **Lihat Detail & Status Pengajuan**, termasuk timeline riwayat verifikasi + pencairan (data verifikasi/ppk/bendahara/ppspm ditampilkan **read-only** di sini — query-nya kamu buat, tapi datanya baru terisi setelah Dev 2 selesai dengan modulnya)
- [ ] `GET /pegawai/pengajuan/{id}/edit` & `PUT /pegawai/pengajuan/{id}` — **Perbaiki Dokumen (Revisi)**
  - Hanya bisa diakses kalau `id_status` = `REVISI`
  - Setelah disimpan, `id_status` kembali ke `VERIFIKASI_1` (mulai ulang dari verifikator 1) atau ke tahap yang mengembalikan — **koordinasikan aturan ini dengan Dev 2**, lihat bagian 8.
- [ ] `GET /pegawai/pengajuan/riwayat` — **Riwayat Pengajuan** (semua pengajuan termasuk yang sudah selesai/ditolak)
- [ ] Logout (sudah include dari Breeze)

### Fase 4 — Manajemen User (Admin, nice-to-have jika waktu cukup)
- [ ] CRUD user oleh admin (assign role, khusus verifikator wajib isi `urutan_verifikator`)

---

## 6. Route List Kamu (ringkas)

```
GET   /login, POST /login, POST /logout          (Breeze)
GET   /pegawai/dashboard
GET   /pegawai/pengajuan
GET   /pegawai/pengajuan/create
POST  /pegawai/pengajuan
GET   /pegawai/pengajuan/{id}
GET   /pegawai/pengajuan/{id}/edit
PUT   /pegawai/pengajuan/{id}
GET   /pegawai/pengajuan/riwayat
```

Gunakan **route name** dengan pola `pegawai.pengajuan.index`, `pegawai.pengajuan.create`, dst — Dev 2 akan mengikuti pola yang sama (`verifikator.*`, `ppk.*`, `bendahara.*`, `ppspm.*`) supaya konsisten.

---

## 7. Urutan Kerja & Titik Koordinasi dengan Dev 2

1. **Kamu duluan**: selesaikan Fase 0 (migration + seeder) dan push. Ini blocking — Dev 2 tidak bisa mulai coding model/controller sebelum tabel ada.
2. Kamu lanjut Fase 1–2 (auth, layout, komponen) sambil Dev 2 mulai bikin Model + relasi Eloquent untuk `verifikasi`, `ppk`, `bendahara`, `ppspm` di branch terpisah.
3. Fase 3 (modul Pegawai) kamu kerjakan paralel dengan modul Verifikasi milik Dev 2 — tidak saling blocking karena beda controller/route, tapi **model `Pengajuan` dan perubahan `id_status`-nya harus disepakati bersama** (lihat bagian 8, "State Machine Status").
4. Sebelum merge ke `develop`, selalu tarik migration/model terbaru dari Dev 2 dan test alur end-to-end: **Ajukan → Verifikasi 1-3 → PPK → Bendahara → PPSPM → Selesai**.

---

## 8. Keputusan Teknis Bersama (Baca sebelum mulai — sama persis di dokumen Dev 2)

- **`urutan_verifikator` di tabel `users`**: tambahan di luar ERD asli, dipakai supaya sistem tahu Verifikator mana yang bertugas di tahap 1/2/3. Kamu yang isi via seeder, Dev 2 yang pakai di logic verifikasi.
- **State machine status pengajuan** (siapa yang mengubah `pengajuan.id_status`, kolom di tabel `pengajuan`):
  - `DIAJUKAN` → diset oleh kamu (Fase 3) saat pegawai submit
  - `VERIFIKASI_1` / `VERIFIKASI_2` / `VERIFIKASI_3` → diubah oleh Dev 2 saat verifikator ACC di tahap sebelumnya
  - `REVISI` → diubah oleh Dev 2 saat verifikator REVISI, lalu **kamu** yang menyediakan halaman edit untuk pegawai (Fase 3), dan setelah pegawai submit ulang, **status kembali ke `VERIFIKASI_1`** (verifikasi diulang dari awal — ini keputusan default, silakan diskusikan dengan tim jika ingin kembali ke tahap terakhir yang mereview saja)
  - `DITOLAK` → final, diubah oleh Dev 2, tidak bisa diedit pegawai lagi
  - `PROSES_PPK` → diubah oleh Dev 2 saat Verifikator 3 ACC
  - `PROSES_BENDAHARA`, `PROSES_PPSPM`, `SELESAI` → diubah oleh Dev 2 sesuai alur pencairan
- **Field yang dibaca Dev 2 dari tabel `pengajuan`**: `id_status`, `total_nominal`, `no_pengajuan`, `id_user` — jangan ganti nama kolom ini tanpa koordinasi.
- **Notifikasi**: untuk versi awal, cukup tampilkan di halaman detail pengajuan (tidak perlu email/push notification). Tabel `catatan_pengaju` (di `pengajuan`) dan `catatan` (di `verifikasi`/`ppk`/`bendahara`/`ppspm`) adalah tempat feedback antar pihak — pastikan field ini selalu ditampilkan di timeline.

---

## 9. Definition of Done

- [ ] `php artisan migrate:fresh --seed` berjalan tanpa error
- [ ] Login redirect benar sesuai role
- [ ] Pegawai bisa: ajukan, lihat daftar, lihat detail, revisi, lihat riwayat — semua sesuai warna & badge status yang konsisten
- [ ] Semua komponen reusable (`x-status-badge`, `x-timeline-item`, `x-app-layout`) sudah dipakai dan didokumentasikan cara pakainya (tulis contoh singkat di `README.md` project)
- [ ] Tidak ada hardcode warna di luar token Tailwind yang disepakati
- [ ] Sudah diuji end-to-end bersama Dev 2 minimal 1x sebelum dianggap selesai
