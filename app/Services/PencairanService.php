<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\Ppk;
use App\Models\Bendahara;
use App\Models\Ppspm;
use Illuminate\Support\Facades\DB;

class PencairanService
{
    public function prosesPpk(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            Ppk::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'tanggal_proses' => now(),
                'id_status' => $data['id_status_pencairan'], 
                'catatan' => $data['catatan'] ?? null,
            ]);

            // Jika Sesuai -> 8 (PROSES_BENDAHARA), Jika Tidak Sesuai -> 4 (VERIFIKASI_3)
            $nextStatus = ($data['id_status_pencairan'] == 1) ? 8 : 4;
            $pengajuan->update(['id_status' => $nextStatus]);

            return $pengajuan;
        });
    }

    public function prosesBendahara(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            Bendahara::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'tanggal_proses' => now(),
                'id_status' => $data['id_status_pencairan'], // ID status Selesai untuk Bendahara
                'catatan' => $data['catatan'] ?? null,
            ]);

            // Bendahara selesai -> 9 (PROSES_PPSPM)
            $pengajuan->update(['id_status' => 9]);

            return $pengajuan;
        });
    }

    public function prosesPpspm(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            Ppspm::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'tanggal_proses' => now(),
                'id_status' => $data['id_status_pencairan'], // ID status Selesai untuk PPSPM
                'catatan' => $data['catatan'] ?? null,
            ]);

            // PPSPM selesai -> 10 (SELESAI)
            $pengajuan->update(['id_status' => 10]);

            return $pengajuan;
        });
    }
}