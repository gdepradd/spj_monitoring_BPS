<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\Ppk;
use App\Models\Bendahara;
use App\Models\Ppspm;
use App\Models\StatusPengajuan;
use Illuminate\Support\Facades\DB;

class PencairanService
{
    public function pilihMetodePembayaran(Pengajuan $pengajuan, string $metode)
    {
        return DB::transaction(function () use ($pengajuan, $metode) {
            $pengajuan->update(['metode_pembayaran' => $metode]);
            return $pengajuan;
        });
    }

    public function tolakAtauRevisiBendahara(Pengajuan $pengajuan, array $data, string $tahap)
    {
        return DB::transaction(function () use ($pengajuan, $data, $tahap) {
            Bendahara::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_user' => auth()->id(),
                'tahap' => $tahap,
                'tanggal_proses' => now(),
                'id_status' => $data['id_status_pencairan'],
                'catatan' => $data['catatan'] ?? null,
            ]);

            $kodeGlobal = $data['id_status_pencairan'] == 2 ? 'REVISI' : 'DITOLAK';
            $statusGlobal = StatusPengajuan::where('kode_status', $kodeGlobal)->firstOrFail();
            $pengajuan->update(['id_status' => $statusGlobal->id_status]);

            return $pengajuan;
        });
    }

    public function bendaharaAjukanSpp(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            Bendahara::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_user' => auth()->id(),
                'tahap' => 'PENGAJUAN_SPP',
                'tanggal_proses' => now(),
                'id_status' => 1,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $statusPpk = StatusPengajuan::where('kode_status', 'PROSES_PPK')->firstOrFail();
            $pengajuan->update(['id_status' => $statusPpk->id_status]);

            return $pengajuan;
        });
    }

    public function bendaharaBayarLangsung(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            Bendahara::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_user' => auth()->id(),
                'tahap' => 'PEMBAYARAN_LANGSUNG',
                'tanggal_proses' => now(),
                'id_status' => 1,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $statusSelesai = StatusPengajuan::where('kode_status', 'SELESAI')->firstOrFail();
            $pengajuan->update(['id_status' => $statusSelesai->id_status]);

            return $pengajuan;
        });
    }

    public function ppkTerbitkanSpm(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            Ppk::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_user' => auth()->id(),
                'tanggal_proses' => now(),
                'id_status' => 1,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $statusPpspm = StatusPengajuan::where('kode_status', 'PROSES_PPSPM')->firstOrFail();
            $pengajuan->update(['id_status' => $statusPpspm->id_status]);

            return $pengajuan;
        });
    }

    public function ppspmAjukanKemenkeu(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            Ppspm::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_user' => auth()->id(),
                'tanggal_proses' => now(),
                'id_status' => 1,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $statusKonfirmasi = StatusPengajuan::where('kode_status', 'PROSES_KONFIRMASI_BENDAHARA')->firstOrFail();
            $pengajuan->update(['id_status' => $statusKonfirmasi->id_status]);

            return $pengajuan;
        });
    }

    public function bendaharaKonfirmasi(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            Bendahara::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_user' => auth()->id(),
                'tahap' => 'KONFIRMASI',
                'tanggal_proses' => now(),
                'id_status' => 1,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $statusSelesai = StatusPengajuan::where('kode_status', 'SELESAI')->firstOrFail();
            $pengajuan->update(['id_status' => $statusSelesai->id_status]);

            return $pengajuan;
        });
    }
}