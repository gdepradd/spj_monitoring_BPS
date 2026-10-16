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

        [$divisi, $pengajuanDivisi] = $this->pengajuanSatuDivisi(Auth::user());

        return view('pegawai.dashboard', compact(
            'statusCounts',
            'total',
            'aktif',
            'revisi',
            'selesai',
            'divisi',
            'pengajuanDivisi'
        ));
    }

    /**
     * Pengajuan rekan satu divisi, untuk panel di bawah ringkasan pengajuan pribadi.
     *
     * Pengajuan milik sendiri dikecualikan karena sudah tampil di panel atas, dan
     * pengajuan berstatus DITOLAK ikut ditampilkan agar progres terbaca apa adanya.
     * User tanpa divisi mendapat koleksi kosong sehingga panelnya tidak dirender.
     *
     * @return array{0: ?\App\Models\Divisi, 1: \Illuminate\Support\Collection}
     */
    private function pengajuanSatuDivisi(User $user): array
    {
        if ($user->id_divisi === null) {
            return [null, collect()];
        }

        $pengajuan = Pengajuan::query()
            ->with(['user:id_user,nama_lengkap', 'status'])
            ->where('id_user', '!=', $user->id_user)
            ->whereHas('user', fn ($query) => $query->where('id_divisi', $user->id_divisi))
            ->orderByDesc('id_pengajuan')
            ->limit(10)
            ->get();

        return [$user->divisi, $pengajuan];
    }
}