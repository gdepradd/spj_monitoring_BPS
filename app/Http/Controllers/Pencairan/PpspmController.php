<?php

namespace App\Http\Controllers\Pencairan;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Services\PencairanService;
use Illuminate\Http\Request;

class PpspmController extends Controller
{
    protected $pencairanService;

    public function __construct(PencairanService $pencairanService)
    {
        $this->pencairanService = $pencairanService;
    }

    public function dashboard()
    {
        $totalMenunggu = Pengajuan::where('id_status', 9)->count();
        return view('ppspm.dashboard', compact('totalMenunggu'));
    }

    public function index()
    {
        $pengajuan = Pengajuan::with('user')->where('id_status', 9)->get();
        return view('ppspm.pengajuan.index', compact('pengajuan'));
    }

    public function show($id)
    {
        $pengajuan = Pengajuan::with(['user', 'bendahara'])->findOrFail($id);
        if ($pengajuan->id_status != 9) abort(403);

        return view('ppspm.pengajuan.show', compact('pengajuan'));
    }

    public function selesai(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $data = ['id_status_pencairan' => 4, 'catatan' => $request->catatan]; // Asumsi 4 = Selesai PPSPM
        $this->pencairanService->prosesPpspm($pengajuan, $data);

        return redirect()->route('ppspm.pengajuan.index')->with('success', 'Arsip PPSPM selesai.');
    }
}