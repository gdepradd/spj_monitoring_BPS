<?php

namespace Database\Seeders;

use App\Models\StatusPengajuan;
use Illuminate\Database\Seeder;

class StatusPengajuanSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'kode_status' => 'DIAJUKAN',
                'nama_status' => 'Diajukan',
                'keterangan' => 'Baru diajukan pegawai',
                'urutan' => 1,
            ],
            [
                'kode_status' => 'VERIFIKASI_1',
                'nama_status' => 'Verifikasi Tahap 1',
                'keterangan' => 'Menunggu Verifikator 1',
                'urutan' => 2,
            ],
            [
                'kode_status' => 'VERIFIKASI_2',
                'nama_status' => 'Verifikasi Tahap 2',
                'keterangan' => 'Menunggu Verifikator 2',
                'urutan' => 3,
            ],
            [
                'kode_status' => 'VERIFIKASI_3',
                'nama_status' => 'Verifikasi Tahap 3',
                'keterangan' => 'Menunggu Verifikator 3',
                'urutan' => 4,
            ],
            [
                'kode_status' => 'REVISI',
                'nama_status' => 'Revisi',
                'keterangan' => 'Dikembalikan ke pegawai',
                'urutan' => 5,
            ],
            [
                'kode_status' => 'DITOLAK',
                'nama_status' => 'Ditolak',
                'keterangan' => 'Final, tidak lanjut',
                'urutan' => 6,
            ],
            [
                'kode_status' => 'MENUNGGU_PENCAIRAN',
                'nama_status' => 'Menunggu Pencairan',
                'keterangan' => 'Lolos verifikasi, menunggu Bendahara memilih metode pembayaran',
                'urutan' => 7,
            ],
            [
                'kode_status' => 'PROSES_PPK',
                'nama_status' => 'Proses PPK',
                'keterangan' => 'Menunggu PPK terbitkan SPM',
                'urutan' => 8,
            ],
            [
                'kode_status' => 'PROSES_PPSPM',
                'nama_status' => 'Proses PPSPM',
                'keterangan' => 'Menunggu PPSPM ajukan ke Kemenkeu',
                'urutan' => 9,
            ],
            [
                'kode_status' => 'PROSES_KONFIRMASI_BENDAHARA',
                'nama_status' => 'Konfirmasi Bendahara',
                'keterangan' => 'Menunggu konfirmasi akhir dari Bendahara',
                'urutan' => 10,
            ],
            [
                'kode_status' => 'SELESAI',
                'nama_status' => 'Selesai',
                'keterangan' => 'Dana sudah cair ke penerima',
                'urutan' => 11,
            ],
        ];

        foreach ($statuses as $status) {
            StatusPengajuan::updateOrCreate(
                ['kode_status' => $status['kode_status']],
                $status
            );
        }
    }
}