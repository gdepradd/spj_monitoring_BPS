<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
   public function index()
{
    $urutan = auth()->user()->urutan_verifikator;

    $query = Pengajuan::with([
        'pemohon',
        'status',
    ]);

    if ($urutan == 1) {
        $query->whereIn('id_status', [1, 2]);
    } elseif ($urutan == 2) {
        $query->where('id_status', 3);
    } elseif ($urutan == 3) {
        $query->where('id_status', 4);
    } else {
        $query->whereRaw('1 = 0');
    }

    $pengajuan = $query
        ->orderBy('tanggal_pengajuan', 'asc')
        ->get();

    $totalMenunggu = $pengajuan->count();

    return view('verifikator.dashboard', compact(
        'pengajuan',
        'totalMenunggu',
        'urutan'
    ));
}
}