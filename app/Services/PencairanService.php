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
            $caraBayar = ($data['cara_bayar'] ?? 'TRANSFER') === 'TUNAI' ? 'TUNAI' : 'TRANSFER';
            $tunai = $caraBayar === 'TUNAI';

            Bendahara::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_user' => auth()->id(),
                'tahap' => 'PEMBAYARAN_LANGSUNG',
                'tanggal_proses' => now(),
                'id_status' => StatusPencairan::ACC,
                'cara_bayar' => $caraBayar,
                // tgl_transfer menampung tanggal uang keluar untuk kedua cara bayar.
                'tgl_transfer' => $data['tgl_transfer'] ?? null,
                // Data rekening hanya relevan pada transfer, kwitansi hanya pada tunai.
                'nama_bank' => $tunai ? null : ($data['nama_bank'] ?? null),
                'no_rekening' => $tunai ? null : ($data['no_rekening'] ?? null),
                'atas_nama_rekening' => $tunai ? null : ($data['atas_nama_rekening'] ?? null),
                'no_kwitansi' => $tunai ? ($data['no_kwitansi'] ?? null) : null,
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
                // PPSPM menerbitkan SPM saja; SP2D dicatat Bendahara saat konfirmasi.
                'no_spm' => $data['no_spm'] ?? null,
                'tgl_spm' => $data['tgl_spm'] ?? null,
            ]);

            $statusKonfirmasi = StatusPengajuan::where('kode_status', 'PROSES_KONFIRMASI_BENDAHARA')->firstOrFail();
            $pengajuan->update(['id_status' => $statusKonfirmasi->id_status]);

            return $pengajuan;
        });
    }

   public function bendaharaKonfirmasi(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            // Nomor SPM diterbitkan PPSPM; SP2D diinput Bendahara pada tahap ini.
            $ppspm = $pengajuan->ppspm()->orderByDesc('id_ppspm')->first();

            // LS Pihak Ketiga selalu transfer oleh Kemenkeu; LS Bendahara mengikuti pilihan.
            $caraBayar = $pengajuan->metode_pembayaran === 'LS_PIHAK_KETIGA'
                ? 'TRANSFER'
                : (($data['cara_bayar'] ?? 'TRANSFER') === 'TUNAI' ? 'TUNAI' : 'TRANSFER');

            $tunai = $caraBayar === 'TUNAI';

            Bendahara::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_user' => auth()->id(),
                'tahap' => 'KONFIRMASI',
                'tanggal_proses' => now(),
                'id_status' => StatusPencairan::ACC,
                'no_spm' => $data['no_spm'] ?? $ppspm?->no_spm,
                // SP2D kini bersumber dari input Bendahara, bukan lagi dari PPSPM.
                'no_sp2d' => $data['no_sp2d'] ?? null,
                'tgl_sp2d' => $data['tgl_sp2d'] ?? null,
                'cara_bayar' => $caraBayar,
                // tgl_transfer menampung tanggal uang keluar untuk semua jalur.
                'tgl_transfer' => $data['tgl_transfer'] ?? null,
                // Rekening hanya relevan pada transfer, kwitansi hanya pada tunai.
                'nama_bank' => $tunai ? null : ($data['nama_bank'] ?? null),
                'no_rekening' => $tunai ? null : ($data['no_rekening'] ?? null),
                'atas_nama_rekening' => $tunai ? null : ($data['atas_nama_rekening'] ?? null),
                'no_kwitansi' => $tunai ? ($data['no_kwitansi'] ?? null) : null,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $statusSelesai = StatusPengajuan::where('kode_status', 'SELESAI')->firstOrFail();
            $pengajuan->update(['id_status' => $statusSelesai->id_status]);

            return $pengajuan;
        });
    }
}