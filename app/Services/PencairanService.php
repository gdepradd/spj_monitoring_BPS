<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\Ppk;
use App\Models\Bendahara;
use App\Models\Ppspm;
use App\Models\StatusPencairan;
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

            $kodeGlobal = (int) $data['id_status_pencairan'] === StatusPencairan::REVISI ? 'REVISI' : 'DITOLAK';
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
                'id_status' => StatusPencairan::ACC,
                'no_spp' => $data['no_spp'] ?? null,
                'tgl_spp' => $data['tgl_spp'] ?? null,
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
                'id_status' => StatusPencairan::ACC,
                'tgl_transfer' => $data['tgl_transfer'] ?? null,
                'nama_bank' => $data['nama_bank'] ?? null,
                'no_rekening' => $data['no_rekening'] ?? null,
                'atas_nama_rekening' => $data['atas_nama_rekening'] ?? null,
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
                'id_status' => StatusPencairan::ACC,
                'no_spm' => $data['no_spm'] ?? null,
                'tgl_spm' => $data['tgl_spm'] ?? null,
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
                'id_status' => StatusPencairan::ACC,
                'tgl_ajukan_kemenkeu' => $data['tgl_ajukan_kemenkeu'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'no_spm' => $data['no_spm'] ?? null,
            'tgl_spm' => $data['tgl_spm'] ?? null,
            'no_sp2d' => $data['no_sp2d'] ?? null,
            'tgl_sp2d' => $data['tgl_sp2d'] ?? null,
            ]);

            $statusKonfirmasi = StatusPengajuan::where('kode_status', 'PROSES_KONFIRMASI_BENDAHARA')->firstOrFail();
            $pengajuan->update(['id_status' => $statusKonfirmasi->id_status]);

            return $pengajuan;
        });
    }

   public function bendaharaKonfirmasi(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            // Nomor SPM/SP2D berasal dari PPSPM; request hanya dipakai sebagai penimpa manual.
            $ppspm = $pengajuan->ppspm()->orderByDesc('id_ppspm')->first();

            Bendahara::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_user' => auth()->id(),
                'tahap' => 'KONFIRMASI',
                'tanggal_proses' => now(),
                'id_status' => StatusPencairan::ACC,
                'no_spm' => $data['no_spm'] ?? $ppspm?->no_spm,
                'no_sp2d' => $data['no_sp2d'] ?? $ppspm?->no_sp2d,
                'tgl_sp2d' => $data['tgl_sp2d'] ?? $ppspm?->tgl_sp2d,
                'tgl_transfer' => $data['tgl_transfer'] ?? null,
                'nama_bank' => $data['nama_bank'] ?? null,
                'no_rekening' => $data['no_rekening'] ?? null,
                'atas_nama_rekening' => $data['atas_nama_rekening'] ?? null,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $statusSelesai = StatusPengajuan::where('kode_status', 'SELESAI')->firstOrFail();
            $pengajuan->update(['id_status' => $statusSelesai->id_status]);

            return $pengajuan;
        });
    }
}