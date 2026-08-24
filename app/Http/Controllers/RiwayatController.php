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
        ])->findOrFail($id);

        $user = Auth::user();

        // Pegawai hanya boleh melihat riwayat pengajuannya sendiri.
        if ($user->isRole('pegawai') && $pengajuan->id_user !== $user->id_user) {
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
}
