<?php

namespace App\Http\Controllers\Pencairan;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\Ppspm;
use App\Models\StatusPengajuan;
use App\Services\PencairanService;
use Illuminate\Http\Request;

class PpspmController extends Controller
{
    protected $pencairanService;

    public function __construct(PencairanService $pencairanService) { $this->pencairanService = $pencairanService; }
    
    public function dashboard()
    {
        $idPpspm = StatusPengajuan::where('kode_status', 'PROSES_PPSPM')->value('id_status');
        
        // Ambil seluruh data pengajuan untuk PPSPM
        $pengajuan = Pengajuan::where('id_status', $idPpspm)->get();
        $totalMenunggu = $pengajuan->count();

        return view('ppspm.dashboard', compact('totalMenunggu', 'pengajuan'));
    }

    public function index()
    {
        $idPpspm = StatusPengajuan::where('kode_status', 'PROSES_PPSPM')->value('id_status');
        $pengajuan = Pengajuan::with('user')->where('id_status', $idPpspm)->get();
        return view('ppspm.pengajuan.index', compact('pengajuan'));
    }

    public function show($id)
    {
        $pengajuan = Pengajuan::with('user')->findOrFail($id);
        return view('ppspm.pengajuan.show', compact('pengajuan'));
    }

    public function ajukanKemenkeu(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $this->pencairanService->ppspmAjukanKemenkeu($pengajuan, $request->all());
        return redirect()->route('ppspm.pengajuan.index')->with('success', 'Diajukan ke Kemenkeu.');
    }

    public function riwayat()
    {
        $riwayat = Ppspm::where('id_user', auth()->id())->with('pengajuan.user')->latest()->paginate(15);
        return view('ppspm.riwayat', compact('riwayat'));
    }
}