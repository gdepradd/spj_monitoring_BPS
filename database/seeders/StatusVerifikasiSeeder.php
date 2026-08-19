<?php

namespace Database\Seeders;

use App\Models\StatusVerifikasi;
use Illuminate\Database\Seeder;

class StatusVerifikasiSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu', 'keterangan' => 'Belum diverifikasi.'],
            ['kode_status' => 'ACC', 'nama_status' => 'ACC', 'keterangan' => 'Pengajuan disetujui pada tahap verifikasi.'],
            ['kode_status' => 'REVISI', 'nama_status' => 'Revisi', 'keterangan' => 'Pengajuan harus diperbaiki oleh pegawai.'],
            ['kode_status' => 'TOLAK', 'nama_status' => 'Tolak', 'keterangan' => 'Pengajuan ditolak.'],
        ];

        foreach ($statuses as $status) {
            StatusVerifikasi::updateOrCreate(['kode_status' => $status['kode_status']], $status);
        }
    }
}
