<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    /**
     * Detail riwayat lengkap sebuah pengajuan.
     * Dipakai bersama oleh seluruh role (pegawai, verifikator, bendahara, ppk, ppspm, admin)
     * sehingga riwayat dari verifikasi sampai pencairan terlihat sama di semua role.
     */
    public function show($id): View
    {
        $pengajuan = Pengajuan::with([
            'user',
            'status',
            'verifikasi' => fn ($query) => $query->orderBy('tahap'),
            'verifikasi.verifikator',
            'verifikasi.statusVerifikasi',
            'bendahara.user',
            'ppk.user',
            'ppspm.user',
            'penerima',
            'tim',
        ])->findOrFail($id);

        $user = Auth::user();

        // Pegawai boleh melihat riwayat pengajuannya sendiri, dan pengajuan rekan
        // satu tim — tombol "Lihat Detail" pada panel tim di dashboard menuju ke
        // sini. Pengajuan di luar keduanya tetap tertutup.
        if ($user->isRole('pegawai') && ! $this->bolehDilihatPegawai($user, $pengajuan)) {
            abort(403, 'Anda tidak memiliki akses ke riwayat pengajuan ini.');
        }

        $spp = $pengajuan->bendahara
            ->where('tahap', 'PENGAJUAN_SPP')
            ->sortByDesc('id_bendahara')
            ->first();

        $pembayaranLangsung = $pengajuan->bendahara
            ->where('tahap', 'PEMBAYARAN_LANGSUNG')
            ->sortByDesc('id_bendahara')
            ->first();

        $konfirmasi = $pengajuan->bendahara
            ->where('tahap', 'KONFIRMASI')
            ->sortByDesc('id_bendahara')
            ->first();

        $ppk = $pengajuan->ppk->sortByDesc('id_ppk')->first();
        $ppspm = $pengajuan->ppspm->sortByDesc('id_ppspm')->first();

        return view('riwayat.detail', compact(
            'pengajuan',
            'spp',
            'pembayaranLangsung',
            'konfirmasi',
            'ppk',
            'ppspm'
        ));
    }

    /**
     * Pengajuan milik sendiri, atau pengajuan yang diajukan atas nama salah satu
     * tim yang diikuti pegawai ini.
     *
     * Penyaringannya memakai pengajuan.id_tim, sejalan dengan panel tim di
     * dashboard, sehingga apa yang bisa diklik sama dengan apa yang bisa dibuka.
     */
    private function bolehDilihatPegawai(\App\Models\User $user, Pengajuan $pengajuan): bool
    {
        if ($pengajuan->id_user === $user->id_user) {
            return true;
        }

        return $pengajuan->id_tim !== null
            && $user->idTim()->contains($pengajuan->id_tim);
    }
}
