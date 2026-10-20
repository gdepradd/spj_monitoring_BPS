<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    /**
     * Notifikasi hanya aktif bila sengaja dinyalakan lewat .env.
     *
     * Diperiksa paling awal — sebelum tabel users disentuh — supaya kode ini
     * aman naik ke server yang migrasinya belum dijalankan. Tanpa penjaga ini,
     * pencarian nomor HP tetap berjalan dan setiap pengiriman pengajuan gagal
     * dengan galat SQL hanya karena kolom no_hp belum ada.
     */
    private static function aktif(): bool
    {
        return (bool) config('services.fonnte.enabled');
    }

    public static function send($target, $message)
    {
        if (! self::aktif() || empty($target)) {
            return false;
        }

        dispatch(function () use ($target, $message) {
            try {
                Http::withHeaders([
                    'Authorization' => config('services.fonnte.token')
                ])->timeout(10)->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                    'countryCode' => '62',
                ]);
            } catch (\Exception $e) {
                Log::error('Fonnte Error: ' . $e->getMessage());
            }
        })->afterResponse();

        return true;
    }

    public static function notifyPengaju(Pengajuan $pengajuan, $message)
    {
        if (! self::aktif()) {
            return;
        }

        $noHp = $pengajuan->user->no_hp ?? null;
        if ($noHp) {
            self::send($noHp, $message);
        }
    }

    public static function notifyRole($roleNames, $message)
    {
        if (! self::aktif()) {
            return;
        }

        $targets = User::getPhonesByRole($roleNames);
        if ($targets) {
            self::send($targets, $message);
        }
    }
    public static function formatDetail(Pengajuan $pengajuan, string $judul, string $statusTeks, ?string $catatan = null, array $infoTambahan = []): string
    {
        $nominal = "Rp " . number_format($pengajuan->total_nominal, 0, ',', '.');
        $pengaju = $pengajuan->user->nama_lengkap ?? $pengajuan->user->name ?? '-';
        $tim = $pengajuan->tim_pengaju ?? '-';
        $tgl = \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d/m/Y');

        // Perihal ditampilkan lebih ringkas agar pesan tidak terlalu panjang.
        $perihal = \Illuminate\Support\Str::limit($pengajuan->keterangan ?? $pengajuan->perihal ?? '-', 120, '...');

        $pesan = "*[{$judul}]*\n\n"
            . "*Detail Pengajuan:*\n"
            . "• No. Pengajuan: *{$pengajuan->no_pengajuan}*\n"
            . "• Tgl. Pengajuan: {$tgl}\n"
            . "• Pengaju: {$pengaju} ({$tim})\n"
            . "• Perihal: {$perihal}\n"
            . "• Total Nominal: *{$nominal}*\n";

        // Jika ada info tambahan seperti No SPP, No SPM, Nominal Cair, dll.
        if (!empty($infoTambahan)) {
            $pesan .= "\n*Informasi Proses:*\n";
            foreach ($infoTambahan as $label => $nilai) {
                if (!empty($nilai)) {
                    $pesan .= "• {$label}: *{$nilai}*\n";
                }
            }
        }

        $pesan .= "\n*Status:* {$statusTeks}\n";

        if (!empty($catatan) && $catatan !== '-') {
            $pesan .= "*Catatan:* _{$catatan}_\n";
        }

        return $pesan;
    }
}
