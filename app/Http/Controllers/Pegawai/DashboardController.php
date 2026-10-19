<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\StatusPengajuan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        $statusCounts = StatusPengajuan::query()
            ->orderBy('urutan')
            ->withCount([
                'pengajuan as jumlah' => fn ($query) =>
                    $query->where('id_user', $userId),
            ])
            ->get();

        $total = Pengajuan::where('id_user', $userId)
            ->count();

        $aktif = Pengajuan::where('id_user', $userId)
            ->whereHas('status', function ($query) {
                $query->whereNotIn(
                    'kode_status',
                    ['SELESAI', 'DITOLAK']
                );
            })
            ->count();

        $revisi = Pengajuan::where('id_user', $userId)
            ->whereHas('status', function ($query) {
                $query->where('kode_status', 'REVISI');
            })
            ->count();

        $selesai = Pengajuan::where('id_user', $userId)
            ->whereHas('status', function ($query) {
                $query->where('kode_status', 'SELESAI');
            })
            ->count();

        [$timSaya, $pengajuanTim] = $this->pengajuanSatuTim(Auth::user());

        return view('pegawai.dashboard', compact(
            'statusCounts',
            'total',
            'aktif',
            'revisi',
            'selesai',
            'timSaya',
            'pengajuanTim'
        ));
    }

    /**
     * Pengajuan rekan satu tim, untuk panel di bawah ringkasan pengajuan pribadi.
     *
     * Satu orang bisa tergabung di beberapa tim, jadi yang ditarik adalah
     * pengajuan dari seluruh tim yang ia ikuti. Penyaringannya memakai
     * pengajuan.id_tim — yaitu tim yang dinyatakan pengaju saat membuat SPJ —
     * bukan keanggotaan pengaju saat ini, supaya pengajuan tidak ikut berpindah
     * ketika seseorang masuk atau keluar tim di kemudian hari.
     *
     * Pengajuan milik sendiri dikecualikan karena sudah tampil di panel atas, dan
     * pengajuan berstatus DITOLAK ikut ditampilkan agar progres terbaca apa adanya.
     * User tanpa tim mendapat koleksi kosong sehingga panelnya tidak dirender.
     *
     * @return array{0: \Illuminate\Database\Eloquent\Collection, 1: \Illuminate\Support\Collection}
     */
    private function pengajuanSatuTim(User $user): array
    {
        $user->loadMissing('tim');
        $idTim = $user->idTim();

        if ($idTim->isEmpty()) {
            return [$user->tim, collect()];
        }

        $pengajuan = Pengajuan::query()
            ->with(['user:id_user,nama_lengkap', 'status', 'tim:id_tim,nama_tim'])
            ->whereIn('id_tim', $idTim)
            ->where('id_user', '!=', $user->id_user)
            ->orderByDesc('id_pengajuan')
            ->limit(10)
            ->get();

        return [$user->tim, $pengajuan];
    }
}
