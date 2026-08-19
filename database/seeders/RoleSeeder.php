<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nama_role' => 'pegawai', 'keterangan' => 'Pegawai/pemohon pengajuan pembayaran'],
            ['nama_role' => 'verifikator', 'keterangan' => 'Verifikator tahap 1, 2, atau 3'],
            ['nama_role' => 'ppk', 'keterangan' => 'Pejabat Pembuat Komitmen'],
            ['nama_role' => 'bendahara', 'keterangan' => 'Bendahara'],
            ['nama_role' => 'ppspm', 'keterangan' => 'PPSPM'],
            ['nama_role' => 'admin', 'keterangan' => 'Administrator aplikasi'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['nama_role' => $role['nama_role']], $role);
        }
    }
}
