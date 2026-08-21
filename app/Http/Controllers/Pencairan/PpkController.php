<?php

namespace App\Http\Controllers\Pencairan;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\Ppk;
use App\Models\StatusPengajuan;
use App\Services\PencairanService;
use Illuminate\Http\Request;

class PpkController extends Controller
{
    protected $pencairanService;

    public function __construct(PencairanService $pencairanService) { $this->pencairanService = $pencairanService; }
    
    public function dashboard()
    {
        $idPpk = StatusPengajuan::where('kode_status', 'PROSES_PPK')->value('id_status');
        
        // Ambil seluruh data pengajuan untuk PPK
        $pengajuan = Pengajuan::where('id_status', $idPpk)->get();
        $totalMenunggu = $pengajuan->count();

        return view('ppk.dashboard', compact('totalMenunggu', 'pengajuan'));
    }

    public function index()
    {
        $idPpk = StatusPengajuan::where('kode_status', 'PROSES_PPK')->value('id_status');
        $pengajuan = Pengajuan::with('user')->where('id_status', $idPpk)->get();
        return view('ppk.pengajuan.index', compact('pengajuan'));
    }

    public function show($id)
    {
        $pengajuan = Pengajuan::with('user')->findOrFail($id);
        return view('ppk.pengajuan.show', compact('pengajuan'));
    }

    public function terbitkanSpm(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $this->pencairanService->ppkTerbitkanSpm($pengajuan, $request->all());
        return redirect()->route('ppk.pengajuan.index')->with('success', 'SPM diterbitkan.');
    }

    public function riwayat()
    {
        $riwayat = Ppk::where('id_user', auth()->id())->with('pengajuan.user')->latest()->paginate(15);
        return view('ppk.riwayat', compact('riwayat'));
    }
}