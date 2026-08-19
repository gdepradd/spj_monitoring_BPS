<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::pluck('id_role', 'nama_role');
        $password = Hash::make('password123');

        $users = [
            ['nama_lengkap' => 'Admin BPS', 'email' => 'admin@bps.test', 'no_hp' => '080000000001', 'role' => 'admin'],
            ['nama_lengkap' => 'Pegawai Satu', 'email' => 'pegawai1@bps.test', 'no_hp' => '080000000002', 'role' => 'pegawai'],
            ['nama_lengkap' => 'Pegawai Dua', 'email' => 'pegawai2@bps.test', 'no_hp' => '080000000003', 'role' => 'pegawai'],
            ['nama_lengkap' => 'Ida', 'email' => 'ida@bps.test', 'no_hp' => '080000000004', 'role' => 'verifikator', 'urutan_verifikator' => 1],
            ['nama_lengkap' => 'Latif', 'email' => 'latif@bps.test', 'no_hp' => '080000000005', 'role' => 'verifikator', 'urutan_verifikator' => 2],
            ['nama_lengkap' => 'Lanna', 'email' => 'lanna@bps.test', 'no_hp' => '080000000006', 'role' => 'verifikator', 'urutan_verifikator' => 3],
            ['nama_lengkap' => 'PPK BPS', 'email' => 'ppk@bps.test', 'no_hp' => '080000000007', 'role' => 'ppk'],
            ['nama_lengkap' => 'Bendahara BPS', 'email' => 'bendahara@bps.test', 'no_hp' => '080000000008', 'role' => 'bendahara'],
            ['nama_lengkap' => 'PPSPM BPS', 'email' => 'ppspm@bps.test', 'no_hp' => '080000000009', 'role' => 'ppspm'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'nama_lengkap' => $data['nama_lengkap'],
                    'password' => $password,
                    'no_hp' => $data['no_hp'],
                    'id_role' => $role[$data['role']],
                    'status_aktif' => true,
                    'urutan_verifikator' => $data['urutan_verifikator'] ?? null,
                ]
            );
        }
    }
}
