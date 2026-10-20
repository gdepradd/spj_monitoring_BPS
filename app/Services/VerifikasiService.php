<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\StatusPengajuan;
use App\Models\StatusVerifikasi;
use App\Models\Verifikasi;
use App\Services\FonnteService;
use Illuminate\Support\Facades\DB;

class VerifikasiService
{
    public function prosesKeputusan(
        Pengajuan $pengajuan,
        array $data,
        int $tahapVerifikator
    ): Pengajuan {
        $pengajuan = DB::transaction(function () use (
            $pengajuan,
            $data,
            $tahapVerifikator
        ) {

            /*
            |--------------------------------------------------------------------------
            | Ambil status keputusan berdasarkan ID yang dikirim form
            |--------------------------------------------------------------------------
            */
            $statusVerifikasi = StatusVerifikasi::findOrFail(
                $data['id_status_verifikasi']
            );

            /*
            |--------------------------------------------------------------------------
            | Simpan riwayat verifikasi
            |--------------------------------------------------------------------------
            */
            Verifikasi::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_verifikator' => auth()->id(),
                'tahap' => $tahapVerifikator,
                'tanggal_verifikasi' => now(),
                'id_status_verifikasi' => $statusVerifikasi->id_status_verifikasi,
                'catatan' => $data['catatan'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Tentukan status pengajuan berikutnya berdasarkan KODE
            |--------------------------------------------------------------------------
            */
            $kodeStatusBerikutnya = match ($statusVerifikasi->kode_status) {

                'ACC' => match ($tahapVerifikator) {
                    1 => 'VERIFIKASI_2',
                    2 => 'VERIFIKASI_3',

                    /*
                     * PERUBAHAN ISSUE BARU:
                     *
                     * Dulu:
                     * Verifikator 3 ACC → PROSES_PPK
                     *
                     * Sekarang:
                     * Verifikator 3 ACC → MENUNGGU_PENCAIRAN
                     *
                     * Bendahara menjadi pintu masuk proses pencairan.
                     */
                    3 => 'MENUNGGU_PENCAIRAN',

                    default => throw new \InvalidArgumentException(
                        'Tahap verifikator tidak valid.'
                    ),
                },

                'REVISI' => 'REVISI',

                'TOLAK' => 'DITOLAK',

                default => throw new \InvalidArgumentException(
                    'Keputusan verifikasi tidak valid.'
                ),
            };

            /*
            |--------------------------------------------------------------------------
            | Ambil ID status dari tabel status_pengajuan
            |--------------------------------------------------------------------------
            |
            | Tidak menggunakan hardcoded ID 2,3,4,7, dst.
            |
            */
            $statusPengajuan = StatusPengajuan::where(
                'kode_status',
                $kodeStatusBerikutnya
            )->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Update status global pengajuan
            |--------------------------------------------------------------------------
            */
            $pengajuan->update([
                'id_status' => $statusPengajuan->id_status,
            ]);

            return $pengajuan->fresh('status');
        });

        /*
        |--------------------------------------------------------------------------
        | Notifikasi WhatsApp via Fonnte (di luar transaksi agar tidak rollback)
        |--------------------------------------------------------------------------
        */
        $statusVerifikasi = StatusVerifikasi::findOrFail($data['id_status_verifikasi']);
        $catatan = $data['catatan'] ?? '-';
        $kodeKeputusan = $statusVerifikasi->kode_status;
        $namaVerifikator = auth()->user()->nama_lengkap ?? auth()->user()->name ?? 'Verifikator';

        if ($kodeKeputusan === 'REVISI') {
            FonnteService::notifyPengaju(
                $pengajuan,
                FonnteService::formatDetail(
                    $pengajuan,
                    'PENGAJUAN PERLU REVISI',
                    "Dikembalikan oleh Verifikator Tahap {$tahapVerifikator} ({$namaVerifikator}). Silakan perbaiki dan kirim ulang.",
                    $catatan
                )
            );
        } elseif ($kodeKeputusan === 'TOLAK') {
            FonnteService::notifyPengaju(
                $pengajuan,
                FonnteService::formatDetail(
                    $pengajuan,
                    'PENGAJUAN DITOLAK',
                    "Ditolak oleh Verifikator Tahap {$tahapVerifikator} ({$namaVerifikator}).",
                    $catatan
                )
            );
        } elseif ($kodeKeputusan === 'ACC') {
            if ($tahapVerifikator < 3) {
                $tahapBerikutnya = $tahapVerifikator + 1;
                FonnteService::notifyRole(
                    'verifikator',
                    FonnteService::formatDetail(
                        $pengajuan,
                        'MENUNGGU VERIFIKASI TAHAP ' . $tahapBerikutnya,
                        "Disetujui Verifikator Tahap {$tahapVerifikator} ({$namaVerifikator}). Menunggu verifikasi Tahap {$tahapBerikutnya}."
                    )
                );

                // Pengaju ikut dikabari setiap kali pengajuannya maju satu tahap
                FonnteService::notifyPengaju(
                    $pengajuan,
                    FonnteService::formatDetail(
                        $pengajuan,
                        'PENGAJUAN ANDA MAJU',
                        "Disetujui Verifikator Tahap {$tahapVerifikator}. Kini menunggu verifikasi Tahap {$tahapBerikutnya}."
                    )
                );
            } else {
                // Tahap 3 ACC → masuk ke Bendahara
                FonnteService::notifyRole(
                    'bendahara',
                    FonnteService::formatDetail(
                        $pengajuan,
                        'PENGAJUAN SIAP DICAIRKAN',
                        "Lolos seluruh verifikasi (Tahap 1-3). Menunggu tindak lanjut pencairan Anda."
                    )
                );

                FonnteService::notifyPengaju(
                    $pengajuan,
                    FonnteService::formatDetail(
                        $pengajuan,
                        'VERIFIKASI SELESAI',
                        "Lolos seluruh tahap verifikasi. Menunggu tindak lanjut Bendahara."
                    )
                );
            }
        }

        return $pengajuan;
    }
}