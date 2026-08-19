<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\StatusPengajuan;
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

        return view('pegawai.dashboard', compact(
            'statusCounts',
            'total',
            'aktif',
            'revisi',
            'selesai'
        ));
    }
}