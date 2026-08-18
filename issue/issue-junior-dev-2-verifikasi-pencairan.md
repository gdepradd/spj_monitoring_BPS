# ISSUE — Junior Developer 2
## Modul: POV 2 (Verifikasi) & POV 3 (Pencairan — PPK, Bendahara, PPSPM)

> Dokumen ini adalah pasangan dari `issue-junior-dev-1-foundation-pengajuan.md`.
> Kedua dokumen WAJIB dibaca bersama sebelum mulai coding, karena banyak bagian yang saling terhubung (skema database, kode status, warna, penamaan route). **Bagian 3 dan 4 di dokumen ini identik dengan milik Dev 1 — jangan ubah sendiri, kalau ada perubahan harus disepakati berdua.**

---

## 1. Ringkasan Proyek

Web app **monitoring pengajuan pembayaran** kantor, dibangun dengan **Laravel + MySQL**. Tidak ada upload berkas — pegawai hanya mengisi form data pengajuan.

Ada 3 POV (sudut pandang user) sesuai Use Case Diagram:

| POV | Aktor | Tanggung jawab di project ini |
|---|---|---|
| POV 1 — Pengajuan | Pegawai | Dev 1 |
| POV 2 — Verifikasi | Verifikator 1, 2, 3 | **Kamu (Dev 2)** |
| POV 3 — Pencairan | PPK, Bendahara, PPSPM | **Kamu (Dev 2)** |

Kamu membangun **logic approval berlapis**: 3 tahap verifikasi berurutan → PPK → Bendahara → PPSPM. Kamu bekerja di atas fondasi (auth, layout, migration, tabel `pengajuan`) yang dibuat Dev 1. **Tunggu Dev 1 selesai Fase 0 (migration+seeder) sebelum kamu mulai coding controller/model.**

---

## 2. Tech Stack

Sama persis dengan Dev 1:
- Laravel + MySQL
- Blade + Tailwind CSS (Breeze, opsi Blade)
- Alpine.js untuk interaksi kecil
- Middleware role custom (dibuat Dev 1, kamu tinggal pakai: `role:verifikator`, `role:ppk`, `role:bendahara`, `role:ppspm`)

---

## 3. Design System (WAJIB SAMA dengan Dev 1 — jangan buat token warna baru)

### 3.1 Warna per POV

| POV | Token | Hex |
|---|---|---|
| POV 1 — Pengajuan (Pegawai) | `pov-pengajuan` | `#2563EB` (biru) |
| POV 2 — Verifikasi | `pov-verifikasi` | `#16A34A` (hijau) |
| POV 3 — Pencairan | `pov-pencairan` | `#7C3AED` (ungu) |

Gunakan `pov-verifikasi` untuk semua halaman verifikator, dan `pov-pencairan` untuk semua halaman PPK/Bendahara/PPSPM (sidebar, header, tombol utama).

### 3.2 Warna status

| Makna status | Token | Hex |
|---|---|---|
| Menunggu / Diproses | `status-pending` | `#F59E0B` |
| ACC / Sesuai / Disetujui | `status-approved` | `#16A34A` |
| Revisi | `status-revisi` | `#F97316` |
| Ditolak | `status-rejected` | `#DC2626` |
| Selesai / Dicairkan | `status-done` | `#0D9488` |
| Netral / Draft | `status-neutral` | `#64748B` |

Ini sesuai legenda di Use Case Diagram: ACC = hijau, REVISI = oranye, TOLAK = merah.

### 3.3 Komponen reusable (dibuat oleh Dev 1, **kamu pakai ulang, jangan buat versi baru**)

- `<x-status-badge :kode="..." :label="..." />` — dipakai di semua tabel/list punyamu
- `<x-app-layout>` — sidebar otomatis berubah warna sesuai role yang login
- `<x-timeline-item />` — dipakai untuk menampilkan histori verifikasi/pencairan, baik di halamanmu maupun ditarik oleh Dev 1 di halaman detail pengajuan milik pegawai

Kalau butuh komponen baru yang sifatnya khusus untuk verifikasi/pencairan (misal: `<x-decision-buttons>` untuk tombol ACC/REVISI/TOLAK), **buat di file terpisah** dan beri nama jelas, lalu infokan ke Dev 1 supaya tidak dobel.

---

## 4. Skema Database (Referensi — migration dibuat oleh Dev 1, kamu HANYA membuat Model + relasi + logic)

Jangan bikin migration baru untuk tabel-tabel ini. Tunggu Dev 1 push, lalu `git pull` dan buat file Model saja.

**verifikasi**
- `id_verifikasi` PK
- `id_pengajuan` FK → pengajuan
- `id_verifikator` FK → users
- `tahap` (1/2/3)
- `tanggal_verifikasi`
- `id_status_verifikasi` FK → status_verifikasi (`MENUNGGU`, `ACC`, `REVISI`, `TOLAK`)
- `catatan`
- `created_at`

**status_verifikasi** — kode: `MENUNGGU`, `ACC`, `REVISI`, `TOLAK`

**ppk / bendahara / ppspm** (struktur identik)
- `id_ppk` / `id_bendahara` / `id_ppspm` PK
- `id_pengajuan` FK → pengajuan
- `tanggal_proses`
- `id_status` FK → status_pencairan
- `catatan`
- `created_at`

**status_pencairan** — kode: `MENUNGGU`, `SESUAI`, `TIDAK_SESUAI`, `SELESAI`

**Kolom penting di tabel `pengajuan` (dibuat & dimiliki Dev 1, kamu hanya UPDATE `id_status`-nya sesuai alur di bawah):**
`id_pengajuan`, `no_pengajuan`, `id_user`, `total_nominal`, `id_status`, `catatan_pengaju`

**Kolom tambahan di `users` yang kamu pakai:** `urutan_verifikator` (nullable int, 1/2/3) — dipakai untuk menentukan verifikator mana yang jalan di tahap berapa saat query dashboard verifikator.

---

## 5. Alur Logic yang Harus Kamu Implementasikan

Berdasarkan Use Case Diagram, catatan alur:

1. Verifikator 1 → 2 → 3 memeriksa **berurutan**. Verifikator tahap-N hanya boleh bertindak kalau `pengajuan.id_status` sedang berada di tahap-N (`VERIFIKASI_1`/`VERIFIKASI_2`/`VERIFIKASI_3`) — cegah verifikator tahap 2 mengakses pengajuan yang masih di tahap 1.
2. Tiga opsi keputusan verifikator: **ACC**, **REVISI**, **TOLAK**.
   - **ACC** → buat record baru di `verifikasi` (status `ACC`), lalu update `pengajuan.id_status` ke tahap berikutnya (`VERIFIKASI_2`, `VERIFIKASI_3`, atau jika sudah tahap 3 → `PROSES_PPK`).
   - **REVISI** → buat record `verifikasi` (status `REVISI`) dengan `catatan` wajib diisi, update `pengajuan.id_status` = `REVISI`. Pengajuan kembali ke pegawai (ditangani Dev 1 di modulnya).
   - **TOLAK** → buat record `verifikasi` (status `TOLAK`) dengan `catatan` wajib diisi, update `pengajuan.id_status` = `DITOLAK`. Ini status final.
3. Setelah pegawai revisi dan submit ulang (`pengajuan.id_status` kembali ke `VERIFIKASI_1` — lihat kesepakatan di bagian 8 dokumen Dev 1), verifikasi diulang dari tahap 1.
4. **PPK** hanya bisa memproses pengajuan dengan `id_status` = `PROSES_PPK`.
   - **Tidak Sesuai** → catat di `ppk` (status `TIDAK_SESUAI`), pengajuan **dikembalikan ke Verifikator** (`pengajuan.id_status` = `VERIFIKASI_3`, kembali ke tahap terakhir verifikasi, sesuai diagram "Kembalikan/Tolak ke Verifikator").
   - **Sesuai** → catat di `ppk` (status `SESUAI`), update `pengajuan.id_status` = `PROSES_BENDAHARA`.
5. **Bendahara** memproses pembayaran untuk pengajuan dengan `id_status` = `PROSES_BENDAHARA` → setelah selesai, catat di `bendahara` (status `SELESAI`), update `pengajuan.id_status` = `PROSES_PPSPM`.
6. **PPSPM** menerbitkan SPM/dokumen dan mengarsipkan untuk pengajuan dengan `id_status` = `PROSES_PPSPM` → catat di `ppspm` (status `SELESAI`), update `pengajuan.id_status` = `SELESAI`. Ini status final (sukses).

**Penting:** Setiap perubahan `pengajuan.id_status` sebaiknya dibungkus dalam DB transaction (`DB::transaction`) supaya insert record (`verifikasi`/`ppk`/`bendahara`/`ppspm`) dan update status `pengajuan` selalu konsisten.

---

## 6. Scope Pekerjaan Kamu (Checklist)

> **Tunggu sinyal dari Dev 1 bahwa Fase 0 (migration + seeder) selesai sebelum mulai Fase A.**

### Fase A — Model & Relasi Eloquent
- [ ] Model `Verifikasi`, relasi `belongsTo(Pengajuan)`, `belongsTo(User, 'id_verifikator')`, `belongsTo(StatusVerifikasi)`
- [ ] Model `Ppk`, `Bendahara`, `Ppspm`, masing-masing `belongsTo(Pengajuan)`, `belongsTo(StatusPencairan)`
- [ ] Model `StatusVerifikasi`, `StatusPencairan`
- [ ] Tambahkan relasi `hasMany` yang relevan di Model `Pengajuan` milik Dev 1 (koordinasikan — edit file yang sama harus lewat komunikasi langsung, hindari conflict)
- [ ] Buat **Service class** `VerifikasiService` dan `PencairanService` untuk membungkus logic alur di bagian 5 (jangan taruh logic langsung di Controller, supaya gampang di-test dan di-reuse)

### Fase B — Modul Verifikasi (POV 2)
Route prefix: `/verifikator/*`, middleware `role:verifikator`

- [ ] `GET /verifikator/dashboard` — ringkasan jumlah pengajuan menunggu di tahap milik verifikator yang login (cek `urutan_verifikator` user)
- [ ] `GET /verifikator/pengajuan` — **Lihat Pengajuan yang Menunggu Verifikasi** (hanya pengajuan yang `id_status`-nya sesuai tahap verifikator login)
- [ ] `GET /verifikator/pengajuan/{id}` — **Periksa Kelengkapan/Kesesuaian** detail pengajuan + histori verifikasi tahap sebelumnya (kalau ada)
- [ ] `POST /verifikator/pengajuan/{id}/keputusan` — submit keputusan **ACC / REVISI / TOLAK** (pakai `<x-decision-buttons>` atau radio + textarea catatan; catatan wajib jika REVISI/TOLAK)
- [ ] `GET /verifikator/riwayat` — riwayat keputusan yang sudah pernah diambil verifikator ini

### Fase C — Modul Pencairan (POV 3)
Route prefix: `/ppk/*`, `/bendahara/*`, `/ppspm/*`, middleware sesuai role masing-masing

**PPK**
- [ ] `GET /ppk/dashboard`
- [ ] `GET /ppk/pengajuan` — **Lihat Pengajuan Siap PPK** (`id_status` = `PROSES_PPK`)
- [ ] `GET /ppk/pengajuan/{id}` — **Uji & Verifikasi Dokumen**
- [ ] `POST /ppk/pengajuan/{id}/keputusan` — **Sesuai** / **Tidak Sesuai** (catatan wajib jika Tidak Sesuai)

**Bendahara**
- [ ] `GET /bendahara/dashboard`
- [ ] `GET /bendahara/pengajuan` — **Lihat Pengajuan Siap Pembayaran** (`id_status` = `PROSES_BENDAHARA`)
- [ ] `GET /bendahara/pengajuan/{id}` — **Proses Pembayaran**
- [ ] `POST /bendahara/pengajuan/{id}/selesai` — **Selesai Pembayaran**

**PPSPM**
- [ ] `GET /ppspm/dashboard`
- [ ] `GET /ppspm/pengajuan` — **Lihat Pengajuan Siap SPM** (`id_status` = `PROSES_PPSPM`)
- [ ] `GET /ppspm/pengajuan/{id}` — **Terbitkan & Sampaikan SPM/Dokumen Lain**
- [ ] `POST /ppspm/pengajuan/{id}/selesai` — **Arsipkan Dokumen** → set `pengajuan.id_status` = `SELESAI`

### Fase D — Timeline & Integrasi dengan Dev 1
- [ ] Pastikan `<x-timeline-item>` milik Dev 1 bisa menampilkan gabungan data `verifikasi` + `ppk` + `bendahara` + `ppspm` terurut berdasarkan waktu (`created_at`) — buat 1 method di `Pengajuan` model, misalnya `getRiwayatLengkapAttribute()`, yang mengembalikan collection gabungan supaya Dev 1 tinggal pakai di halaman detail pegawai.
- [ ] Uji coba bersama Dev 1: alur penuh dari ajuan pegawai sampai selesai.

---

## 7. Route List Kamu (ringkas)

```
GET   /verifikator/dashboard
GET   /verifikator/pengajuan
GET   /verifikator/pengajuan/{id}
POST  /verifikator/pengajuan/{id}/keputusan
GET   /verifikator/riwayat

GET   /ppk/dashboard
GET   /ppk/pengajuan
GET   /ppk/pengajuan/{id}
POST  /ppk/pengajuan/{id}/keputusan

GET   /bendahara/dashboard
GET   /bendahara/pengajuan
GET   /bendahara/pengajuan/{id}
POST  /bendahara/pengajuan/{id}/selesai

GET   /ppspm/dashboard
GET   /ppspm/pengajuan
GET   /ppspm/pengajuan/{id}
POST  /ppspm/pengajuan/{id}/selesai
```

Route name pattern: `verifikator.pengajuan.index`, `ppk.pengajuan.index`, `bendahara.pengajuan.index`, `ppspm.pengajuan.index`, dst — mengikuti pola yang dipakai Dev 1 di `pegawai.*`.

---

## 8. Keputusan Teknis Bersama (identik dengan dokumen Dev 1 — baca juga di sana)

- **`urutan_verifikator`** di tabel `users`: dipakai untuk tahu verifikator mana bertugas di tahap berapa. Diisi via seeder oleh Dev 1, dipakai di query dashboard kamu (`WHERE urutan_verifikator = X`).
- **State machine status pengajuan** — kamu adalah pemilik logic yang **mengubah** `id_status` untuk sebagian besar alur (`VERIFIKASI_2/3`, `REVISI`, `DITOLAK`, `PROSES_PPK/BENDAHARA/PPSPM`, `SELESAI`). Hanya `DIAJUKAN` (awal) yang diset oleh Dev 1, dan kembalinya dari `REVISI` ke `VERIFIKASI_1` juga dilakukan oleh Dev 1 saat pegawai submit ulang.
- **Field `catatan` wajib diisi** untuk keputusan REVISI, TOLAK, dan Tidak Sesuai — ini yang jadi feedback yang dibaca pegawai di modul Dev 1. Jangan biarkan field ini kosong secara sistem (validasi di FormRequest).
- **Jangan ubah nama kolom** `id_status`, `total_nominal`, `no_pengajuan`, `id_user` di tabel `pengajuan` tanpa koordinasi dengan Dev 1.

---

## 9. Definition of Done

- [ ] Semua Model, relasi, dan Service class berjalan tanpa error
- [ ] Alur lengkap bisa dijalankan: Verifikator 1 ACC → Verifikator 2 ACC → Verifikator 3 ACC → PPK Sesuai → Bendahara Selesai → PPSPM Selesai → `pengajuan.id_status` = `SELESAI`
- [ ] Alur REVISI dan TOLAK dari sisi verifikator bekerja dan tercatat di timeline
- [ ] Alur "Tidak Sesuai" dari PPK mengembalikan pengajuan ke verifikator dengan benar
- [ ] Semua halaman pakai `<x-app-layout>`, `<x-status-badge>`, warna token yang sama dengan Dev 1 (tidak ada warna hardcode baru)
- [ ] Timeline riwayat di halaman detail pegawai (modul Dev 1) menampilkan data dari modulmu dengan benar
- [ ] Sudah diuji end-to-end bersama Dev 1 minimal 1x sebelum dianggap selesai
