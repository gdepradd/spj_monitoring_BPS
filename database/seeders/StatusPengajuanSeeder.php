<?php

namespace Database\Seeders;

use App\Models\StatusPengajuan;
use Illuminate\Database\Seeder;

class StatusPengajuanSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['kode_status' => 'DIAJUKAN', 'nama_status' => 'Diajukan', 'keterangan' => 'Pengajuan baru dibuat oleh pegawai.', 'urutan' => 1],
            ['kode_status' => 'VERIFIKASI_1', 'nama_status' => 'Verifikasi 1', 'keterangan' => 'Menunggu/proses Verifikator 1.', 'urutan' => 2],
            ['kode_status' => 'VERIFIKASI_2', 'nama_status' => 'Verifikasi 2', 'keterangan' => 'Menunggu/proses Verifikator 2.', 'urutan' => 3],
            ['kode_status' => 'VERIFIKASI_3', 'nama_status' => 'Verifikasi 3', 'keterangan' => 'Menunggu/proses Verifikator 3.', 'urutan' => 4],
            ['kode_status' => 'REVISI', 'nama_status' => 'Revisi', 'keterangan' => 'Pengajuan dikembalikan kepada pegawai untuk diperbaiki.', 'urutan' => 5],
            ['kode_status' => 'DITOLAK', 'nama_status' => 'Ditolak', 'keterangan' => 'Pengajuan ditolak dan bersifat final.', 'urutan' => 6],
            ['kode_status' => 'PROSES_PPK', 'nama_status' => 'Proses PPK', 'keterangan' => 'Pengajuan diproses PPK.', 'urutan' => 7],
            ['kode_status' => 'PROSES_BENDAHARA', 'nama_status' => 'Proses Bendahara', 'keterangan' => 'Pengajuan diproses Bendahara.', 'urutan' => 8],
            ['kode_status' => 'PROSES_PPSPM', 'nama_status' => 'Proses PPSPM', 'keterangan' => 'Pengajuan diproses PPSPM.', 'urutan' => 9],
            ['kode_status' => 'SELESAI', 'nama_status' => 'Selesai', 'keterangan' => 'Pengajuan selesai/dicairkan.', 'urutan' => 10],
        ];

        foreach ($statuses as $status) {
            StatusPengajuan::updateOrCreate(['kode_status' => $status['kode_status']], $status);
        }
    }
}
