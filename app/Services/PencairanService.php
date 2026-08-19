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

        /*
         * 2 = SESUAI
         * 3 = TIDAK_SESUAI
         *
         * SESUAI        -> PROSES_BENDAHARA (8)
         * TIDAK SESUAI  -> kembali VERIFIKASI_3 (4)
         */
        if ((int) $data['id_status_pencairan'] === 2) {
            $pengajuan->update([
                'id_status' => 8,
            ]);
        } else {
            $pengajuan->update([
                'id_status' => 4,
            ]);
        }

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
            'id_status' => $data['id_status_pencairan'],
            'catatan' => $data['catatan'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Keputusan PPSPM
        |--------------------------------------------------------------------------
        |
        | 2 = SESUAI
        | 3 = TIDAK_SESUAI
        |
        */

        if ((int) $data['id_status_pencairan'] === 2) {

            // SESUAI → proses selesai
            $pengajuan->update([
                'id_status' => 10,
            ]);

        } else {

            // TIDAK SESUAI
            // Sesuaikan tujuan kembali dengan flow yang Anda inginkan.
            $pengajuan->update([
                'id_status' => 4,
            ]);
        }

        return $pengajuan;
    });
}
}