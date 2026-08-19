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
    $pengajuan = Pengajuan::with([
        'pemohon',
        'status',
    ])
        ->where('id_status', 8)
        ->orderBy('tanggal_pengajuan')
        ->get();

    return view(
        'bendahara.pengajuan.index',
        compact('pengajuan')
    );
}

   public function show($id)
{
    $pengajuan = Pengajuan::with([
        'pemohon',
        'status',
        'verifikasi' => function ($query) {
            $query->orderBy('tahap');
        },
        'verifikasi.verifikator',
        'verifikasi.statusVerifikasi',
    ])->findOrFail($id);

    if ($pengajuan->id_status != 8) {
        abort(403, 'Bukan wewenang Bendahara.');
    }

    $ppk = \App\Models\Ppk::with('statusPencairan')
        ->where('id_pengajuan', $pengajuan->id_pengajuan)
        ->latest('id_ppk')
        ->first();

    return view(
        'bendahara.pengajuan.show',
        compact('pengajuan', 'ppk')
    );
}

   public function keputusan(Request $request, $id)
{
    $pengajuan = Pengajuan::findOrFail($id);

    if ($pengajuan->id_status != 8) {
        abort(403, 'Bukan wewenang Bendahara.');
    }

    $data = $request->validate([
        'id_status_pencairan' => [
            'required',
            'in:2,3',
        ],

        'catatan' => [
            'nullable',
            'required_if:id_status_pencairan,3',
        ],
    ]);

    $this->pencairanService->prosesBendahara(
        $pengajuan,
        $data
    );

    return redirect()
        ->route('bendahara.dashboard')
        ->with(
            'success',
            'Keputusan Bendahara berhasil disimpan.'
        );
}
}