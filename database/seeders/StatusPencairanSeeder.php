<?php

namespace Database\Seeders;

use App\Models\StatusPencairan;
use Illuminate\Database\Seeder;

class StatusPencairanSeeder extends Seeder
{
    /**
     * Id di-seed eksplisit karena tabel bendahara/ppk/ppspm menyimpan
     * id_status sesuai konstanta pada App\Models\StatusPencairan
     * (1 = ACC, 2 = REVISI, 3 = DITOLAK, 4 = SELESAI).
     */
    public function run(): void
    {
        $statuses = [
            StatusPencairan::ACC => ['kode_status' => 'ACC', 'nama_status' => 'ACC', 'keterangan' => 'Disetujui dan diteruskan ke tahap berikutnya.'],
            StatusPencairan::REVISI => ['kode_status' => 'REVISI', 'nama_status' => 'Revisi', 'keterangan' => 'Dikembalikan untuk diperbaiki pengaju.'],
            StatusPencairan::DITOLAK => ['kode_status' => 'DITOLAK', 'nama_status' => 'Ditolak', 'keterangan' => 'Pengajuan ditolak pada tahap pencairan.'],
            StatusPencairan::SELESAI => ['kode_status' => 'SELESAI', 'nama_status' => 'Selesai', 'keterangan' => 'Tahap pencairan selesai.'],
            5 => ['kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu', 'keterangan' => 'Menunggu proses pencairan.'],
        ];

        foreach ($statuses as $id => $status) {
            StatusPencairan::updateOrCreate(
                ['id_status_pencairan' => $id],
                $status
            );
        }
    }
}
