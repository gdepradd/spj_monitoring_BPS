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
    $pengajuan = Pengajuan::with([
        'pemohon',
        'status',
    ])
        ->where('id_status', 9)
        ->orderBy('tanggal_pengajuan', 'asc')
        ->get();

    return view(
        'ppspm.pengajuan.index',
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

    if ((int) $pengajuan->id_status !== 9) {
        abort(403, 'Bukan wewenang PPSPM.');
    }

    $ppk = \App\Models\Ppk::with('statusPencairan')
        ->where('id_pengajuan', $pengajuan->id_pengajuan)
        ->latest('id_ppk')
        ->first();

    $bendahara = \App\Models\Bendahara::with('statusPencairan')
        ->where('id_pengajuan', $pengajuan->id_pengajuan)
        ->latest('id_bendahara')
        ->first();

    return view(
        'ppspm.pengajuan.show',
        compact(
            'pengajuan',
            'ppk',
            'bendahara'
        )
    );
}

   public function keputusan(Request $request, $id)
{
    $pengajuan = Pengajuan::findOrFail($id);

    // Hanya boleh diproses jika memang sedang di tahap PPSPM
    if ((int) $pengajuan->id_status !== 9) {
        abort(403, 'Pengajuan ini tidak berada pada tahap PPSPM.');
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

    $this->pencairanService->prosesPpspm(
        $pengajuan,
        $data
    );

    // Jangan kembali ke halaman show pengajuan yang statusnya sudah berubah.
    return redirect()
        ->route('ppspm.dashboard')
        ->with(
            'success',
            'Keputusan PPSPM berhasil disimpan.'
        );
}
}