<?php

namespace App\Http\Controllers\Pencairan;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Services\PencairanService;
use Illuminate\Http\Request;

class PpkController extends Controller
{
    protected $pencairanService;

    public function __construct(PencairanService $pencairanService)
    {
        $this->pencairanService = $pencairanService;
    }

    public function dashboard()
    {
        $totalMenunggu = Pengajuan::where('id_status', 7)->count();
        return view('ppk.dashboard', compact('totalMenunggu'));
    }

    public function index()
    {
        $pengajuan = Pengajuan::with('user')->where('id_status', 7)->get();
        return view('ppk.pengajuan.index', compact('pengajuan'));
    }

    public function show($id)
    {
        $pengajuan = Pengajuan::with(['user', 'verifikasi.verifikator'])->findOrFail($id);
        
        if ($pengajuan->id_status != 7) abort(403, 'Bukan wewenang PPK.');

        return view('ppk.pengajuan.show', compact('pengajuan'));
    }

    public function keputusan(Request $request, $id)
    {
        $request->validate([
            'id_status_pencairan' => 'required',
            'catatan' => 'required_if:id_status_pencairan,2', // ID 2 = Tidak Sesuai
        ]);

        $pengajuan = Pengajuan::findOrFail($id);
        $this->pencairanService->prosesPpk($pengajuan, $request->all());

        return redirect()->route('ppk.pengajuan.index')->with('success', 'Keputusan PPK diproses.');
    }
}