<?php

namespace App\Http\Controllers\Pencairan;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Services\PencairanService;
use Illuminate\Http\Request;

class BendaharaController extends Controller
{
    protected $pencairanService;

    public function __construct(PencairanService $pencairanService)
    {
        $this->pencairanService = $pencairanService;
    }

    public function dashboard()
    {
        $totalMenunggu = Pengajuan::where('id_status', 8)->count();
        return view('bendahara.dashboard', compact('totalMenunggu'));
    }

    public function index()
    {
        $pengajuan = Pengajuan::with('user')->where('id_status', 8)->get();
        return view('bendahara.pengajuan.index', compact('pengajuan'));
    }

    public function show($id)
    {
        $pengajuan = Pengajuan::with(['user', 'ppk'])->findOrFail($id);
        if ($pengajuan->id_status != 8) abort(403);

        return view('bendahara.pengajuan.show', compact('pengajuan'));
    }

    public function selesai(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $data = ['id_status_pencairan' => 3, 'catatan' => $request->catatan]; // Asumsi 3 = Selesai Bendahara
        $this->pencairanService->prosesBendahara($pengajuan, $data);

        return redirect()->route('bendahara.pengajuan.index')->with('success', 'Pembayaran diproses.');
    }
}