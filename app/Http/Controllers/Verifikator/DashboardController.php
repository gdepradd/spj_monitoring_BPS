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

    $query = Pengajuan::query();

    if ($urutan == 1) {
        // Verifikator 1 membaca DIAJUKAN (1) dan VERIFIKASI_1 (2)
        $query->whereIn('id_status', [1, 2]);
    } elseif ($urutan == 2) {
        $query->where('id_status', 3); // VERIFIKASI_2
    } elseif ($urutan == 3) {
        $query->where('id_status', 4); // VERIFIKASI_3
    }

    $totalMenunggu = $query->count();

    return view('verifikator.dashboard', compact('totalMenunggu', 'urutan'));
}
}