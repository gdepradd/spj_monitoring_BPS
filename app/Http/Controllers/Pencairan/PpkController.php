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
    $pengajuan = Pengajuan::with([
        'pemohon',
        'status',

        'verifikasi' => function ($query) {
            $query->orderBy('tahap');
        },

        'verifikasi.verifikator',
        'verifikasi.statusVerifikasi',
    ])
        ->where('id_status', 7)
        ->orderBy('tanggal_pengajuan', 'asc')
        ->get();

    $totalMenunggu = $pengajuan->count();

    return view('ppk.dashboard', compact(
        'pengajuan',
        'totalMenunggu'
    ));
}

    public function index()
{
    $pengajuan = Pengajuan::with([
        'pemohon',
        'status',
    ])
        ->where('id_status', 7)
        ->orderBy('tanggal_pengajuan', 'asc')
        ->get();

    return view('ppk.pengajuan.index', compact('pengajuan'));
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

    if ($pengajuan->id_status != 7) {
        abort(403, 'Bukan wewenang PPK.');
    }

    return view('ppk.pengajuan.show', compact('pengajuan'));
}

    public function keputusan(Request $request, $id)
    {
        $request->validate([
    'id_status_pencairan' => 'required|in:2,3',
    'catatan' => 'nullable|required_if:id_status_pencairan,3',
]);

        $pengajuan = Pengajuan::findOrFail($id);
        $this->pencairanService->prosesPpk($pengajuan, $request->all());

        return redirect()->route('ppk.pengajuan.index')->with('success', 'Keputusan PPK diproses.');
    }
}