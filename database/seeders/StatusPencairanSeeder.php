<?php

namespace Database\Seeders;

use App\Models\StatusPencairan;
use Illuminate\Database\Seeder;

class StatusPencairanSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu', 'keterangan' => 'Menunggu proses pencairan.'],
            ['kode_status' => 'SESUAI', 'nama_status' => 'Sesuai', 'keterangan' => 'Data dinyatakan sesuai.'],
            ['kode_status' => 'TIDAK_SESUAI', 'nama_status' => 'Tidak Sesuai', 'keterangan' => 'Data dinyatakan tidak sesuai.'],
            ['kode_status' => 'SELESAI', 'nama_status' => 'Selesai', 'keterangan' => 'Tahap pencairan selesai.'],
        ];

        foreach ($statuses as $status) {
            StatusPencairan::updateOrCreate(['kode_status' => $status['kode_status']], $status);
        }
    }
}
