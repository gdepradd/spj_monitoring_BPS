<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Mengisi tabel users dengan pegawai BPS Kabupaten Batang Hari yang sebenarnya.
 *
 * Sumber data: database/seeders/data/pegawai.json, hasil ekstraksi tabel `users`
 * pada dump SIMABAR (data.sql). Pembagian peran verifikator 1-3, bendahara, PPK,
 * dan PPSPM mengikuti role.txt; dua akun superadmin di sistem asal menjadi admin;
 * sisanya pegawai.
 *
 * Password dibawa apa adanya dalam bentuk hash bcrypt dari sistem asal, sehingga
 * setiap pegawai masuk memakai kata sandi yang sudah mereka kenal. Seeder tidak
 * pernah melihat kata sandi aslinya.
 *
 * Aman dijalankan berulang: pencocokan memakai email, dan seeder ini tidak
 * menghapus baris apa pun.
 */
class PegawaiRealSeeder extends Seeder
{
    public function run(): void
    {
        $berkas = database_path('seeders/data/pegawai.json');

        if (! is_file($berkas)) {
            throw new RuntimeException("Berkas data pegawai tidak ditemukan: {$berkas}");
        }

        $daftar = json_decode(file_get_contents($berkas), true, 512, JSON_THROW_ON_ERROR);
        $peranKeId = Role::pluck('id_role', 'nama_role');

        $baru = $diperbarui = 0;

        foreach ($daftar as $p) {
            if (! isset($peranKeId[$p['role']])) {
                throw new RuntimeException("Role '{$p['role']}' belum ada di tabel roles. Jalankan RoleSeeder lebih dulu.");
            }

            $user = User::firstWhere('email', $p['email']);
            $user ? $diperbarui++ : $baru++;

            User::updateOrCreate(
                ['email' => $p['email']],
                [
                    'nama_lengkap'       => $p['nama_lengkap'],
                    'password'           => $p['password'],
                    'id_role'            => $peranKeId[$p['role']],
                    'urutan_verifikator' => $p['urutan_verifikator'],
                    'status_aktif'       => $p['status_aktif'],
                ]
            );
        }

        $this->command?->info("Pegawai: {$baru} ditambahkan, {$diperbarui} diperbarui.");
    }
}
