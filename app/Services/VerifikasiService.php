<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\Verifikasi;
use Illuminate\Support\Facades\DB;

class VerifikasiService
{
    public function prosesKeputusan(Pengajuan $pengajuan, array $data, int $tahapVerifikator)
    {
        return DB::transaction(function () use ($pengajuan, $data, $tahapVerifikator) {
            // 1. Catat ke tabel verifikasi
            Verifikasi::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_verifikator' => auth()->id(),
                'tahap' => $tahapVerifikator,
                'tanggal_verifikasi' => now(),
                'id_status_verifikasi' => $data['id_status_verifikasi'],
                'catatan' => $data['catatan'] ?? null,
            ]);

            // 2. Tentukan ID status pengajuan berikutnya
            // Asumsi ID Status Verifikasi: 1 = ACC, 2 = REVISI, 3 = TOLAK (sesuaikan dengan tabel status_verifikasi)
            $nextStatus = match ((int) $data['id_status_verifikasi']) {
                1 => match ($tahapVerifikator) { // ACC
                    1 => 3, // Lanjut ke VERIFIKASI_2
                    2 => 4, // Lanjut ke VERIFIKASI_3
                    3 => 7, // Lanjut ke PROSES_PPK
                },
                2 => 5, // REVISI
                3 => 6, // DITOLAK
            };

            $pengajuan->update([
                'id_status' => $nextStatus
            ]);

            return $pengajuan;
        });
    }
}