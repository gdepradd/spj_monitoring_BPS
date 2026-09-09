<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\Ppk;
use App\Models\Bendahara;
use App\Models\Ppspm;
use App\Models\StatusPencairan;
use App\Models\StatusPengajuan;
use Illuminate\Support\Collection;
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

    /**
     * UP/TUP: uang dibayarkan langsung oleh Bendahara, bisa penuh atau bertahap.
     */
    public function bendaharaBayarLangsung(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            $this->catatPembayaran($pengajuan, $data, 'PEMBAYARAN_LANGSUNG');
            $this->perbaruiStatusPencairan($pengajuan);

            return $pengajuan;
        });
    }

    /**
     * Pembayaran lanjutan untuk pengajuan yang dicairkan bertahap.
     * Tahapnya mengikuti metode: UP/TUP tetap PEMBAYARAN_LANGSUNG, LS memakai KONFIRMASI.
     */
    public function bendaharaBayarLanjutan(Pengajuan $pengajuan, array $data)
    {
        return DB::transaction(function () use ($pengajuan, $data) {
            $tahap = $pengajuan->metode_pembayaran === 'UP_TUP'
                ? 'PEMBAYARAN_LANGSUNG'
                : 'KONFIRMASI';

            // Nomor SP2D pada jalur LS diwarisi dari catatan konfirmasi pertama.
            $tambahan = [];

            if ($tahap === 'KONFIRMASI') {
                $konfirmasiPertama = $pengajuan->pembayaran()
                    ->where('tahap', 'KONFIRMASI')
                    ->orderBy('id_bendahara')
                    ->first();

                $tambahan = [
                    'no_spm' => $konfirmasiPertama?->no_spm,
                    'no_sp2d' => $konfirmasiPertama?->no_sp2d,
                    'tgl_sp2d' => $konfirmasiPertama?->tgl_sp2d,
                ];
            }

            $this->catatPembayaran($pengajuan, $data, $tahap, $tambahan);
            $this->perbaruiStatusPencairan($pengajuan);

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

            $this->catatPembayaran($pengajuan, $data, 'KONFIRMASI', [
                'no_spm' => $data['no_spm'] ?? $ppspm?->no_spm,
                'no_sp2d' => $data['no_sp2d'] ?? null,
                'tgl_sp2d' => $data['tgl_sp2d'] ?? null,
            ]);

            $this->perbaruiStatusPencairan($pengajuan);

            return $pengajuan;
        });
    }

    /**
     * Catat pembayaran ke penerima.
     *
     * Pencairan parsial boleh dibagi ke beberapa penerima sekaligus: tiap
     * penerima disimpan sebagai satu baris tersendiri dengan nominalnya sendiri.
     * Cara bayar, tanggal, dan nomor kwitansi berlaku sama untuk seluruh baris
     * pada satu pembayaran. Total seluruh baris tidak pernah melebihi sisa tagihan.
     *
     * @return \Illuminate\Support\Collection<int, Bendahara>
     */
    private function catatPembayaran(Pengajuan $pengajuan, array $data, string $tahap, array $tambahan = []): Collection
    {
        // Dihitung ulang lewat query agar tidak memakai relasi yang mungkin sudah basi.
        $dibayar = (float) $pengajuan->pembayaran()->sum('nominal');
        $sisa = max(0, (float) $pengajuan->total_nominal - $dibayar);

        $parsial = ($data['jenis_pencairan'] ?? 'PENUH') === 'PARSIAL';

        // Satu pengajuan memakai satu nomor kwitansi. Bila pembayaran tunai
        // sebelumnya sudah mencatat nomor, termin berikutnya memakai nomor itu
        // dan isian dari form diabaikan.
        $data['no_kwitansi'] = $pengajuan->no_kwitansi ?? ($data['no_kwitansi'] ?? null);

        // Jenis pencairan ditetapkan sekali, saat pembayaran pertama dicatat.
        if (! $pengajuan->jenis_pencairan) {
            $pengajuan->update(['jenis_pencairan' => $parsial ? 'PARSIAL' : 'PENUH']);
        }

        $daftar = $this->susunPenerima($pengajuan, $data, $sisa, $parsial);

        $tercatat = collect();

        foreach ($daftar as $penerima) {
            $tercatat->push(Bendahara::create(array_merge([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_user' => auth()->id(),
                'tahap' => $tahap,
                'tanggal_proses' => now(),
                'id_status' => StatusPencairan::ACC,
                'cara_bayar' => $penerima['cara_bayar'],
                'nominal' => $penerima['nominal'],
                'nama_penerima' => $penerima['nama_penerima'],
                // tgl_transfer menampung tanggal uang keluar untuk semua cara bayar.
                'tgl_transfer' => $data['tgl_transfer'] ?? null,
                'no_kwitansi' => $penerima['cara_bayar'] === 'TUNAI' ? ($penerima['no_kwitansi'] ?? null) : null,
                'catatan' => $data['catatan'] ?? null,
            ], $tambahan)));
        }

        return $tercatat;
    }

    /**
     * Susun daftar penerima beserta nominalnya.
     *
     * Pencairan penuh selalu menghasilkan satu baris sebesar sisa tagihan.
     * Pencairan parsial memakai baris-baris dari input "penerima", dipotong
     * bila akumulasinya melampaui sisa, dan baris bernominal nol dibuang.
     * Cara bayar dan nomor kwitansi seragam untuk seluruh baris.
     */
    private function susunPenerima(Pengajuan $pengajuan, array $data, float $sisa, bool $parsial): array
    {
        $caraBayarTunggal = ($data['cara_bayar'] ?? 'TRANSFER') === 'TUNAI' ? 'TUNAI' : 'TRANSFER';

        if (! $parsial) {
            return [[
                'nama_penerima' => $data['nama_penerima'] ?? $pengajuan->nama_penerima,
                'nominal' => $sisa,
                'cara_bayar' => $caraBayarTunggal,
                'no_kwitansi' => $data['no_kwitansi'] ?? null,
            ]];
        }

        $baris = array_values(array_filter(
            $data['penerima'] ?? [],
            fn ($item) => is_array($item) && (float) ($item['nominal'] ?? 0) > 0
        ));

        // Tanpa rincian penerima, pembayaran parsial jatuh ke satu penerima.
        if (empty($baris)) {
            return [[
                'nama_penerima' => $data['nama_penerima'] ?? $pengajuan->nama_penerima,
                'nominal' => min((float) ($data['nominal'] ?? 0), $sisa),
                'cara_bayar' => $caraBayarTunggal,
                'no_kwitansi' => $data['no_kwitansi'] ?? null,
            ]];
        }

        $hasil = [];
        $tersisa = $sisa;

        foreach ($baris as $item) {
            if ($tersisa <= 0) {
                break;
            }

            $nominal = min((float) $item['nominal'], $tersisa);
            $tersisa -= $nominal;

            $hasil[] = [
                'nama_penerima' => $item['nama_penerima'] ?? $pengajuan->nama_penerima,
                'nominal' => $nominal,
                // Cara bayar dan kwitansi diambil dari pembayaran, bukan per penerima:
                // satu pembayaran memakai satu cara bayar dan satu nomor kwitansi.
                'cara_bayar' => $caraBayarTunggal,
                'no_kwitansi' => $data['no_kwitansi'] ?? null,
            ];
        }

        return $hasil;
    }

    /**
     * Setelah pembayaran dicatat, status pengajuan mengikuti sisa tagihan:
     * lunas menjadi SELESAI, masih bersisa menjadi PENCAIRAN_PARSIAL.
     */
    private function perbaruiStatusPencairan(Pengajuan $pengajuan): void
    {
        $dibayar = (float) $pengajuan->pembayaran()->sum('nominal');
        $lunas = $dibayar >= (float) $pengajuan->total_nominal;

        $status = StatusPengajuan::where('kode_status', $lunas ? 'SELESAI' : 'PENCAIRAN_PARSIAL')->firstOrFail();

        $pengajuan->update(['id_status' => $status->id_status]);
    }
}
