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
        $pengajuan = Pengajuan::with(['user', 'status'])->where('id_status', $idPpspm)->get();
        return view('ppspm.pengajuan.index', compact('pengajuan'));
    }

    public function show($id)
{
    $pengajuan = Pengajuan::with([
        'user',
        'status',
        'verifikasi' => function ($query) {
            $query->orderBy('tahap');
        },
        'verifikasi.verifikator',
        'verifikasi.statusVerifikasi',
        'bendahara.user',
        'ppk.user',
        'ppspm.user',
    ])->findOrFail($id);

    return view('ppspm.pengajuan.show', compact('pengajuan'));
}

    public function ajukanKemenkeu(Request $request, $id)
    {
        $pengajuan = Pengajuan::with('status')->findOrFail($id);

        // Hanya boleh diproses saat pengajuan benar-benar berada di tahap PPSPM.
        if ($pengajuan->status?->kode_status !== 'PROSES_PPSPM') {
            return redirect()
                ->route('ppspm.pengajuan.index')
                ->with('error', 'Pengajuan ini tidak berada pada tahap PPSPM.');
        }

        // PPSPM menerbitkan SPM; nomor SP2D baru dicatat Bendahara saat konfirmasi.
        $data = $request->validate([
            'tgl_ajukan_kemenkeu' => 'required|date',
            'no_spm' => 'required|string|max:100',
            'tgl_spm' => 'required|date',
            'catatan' => 'nullable|string|max:5000',
        ], [
            'tgl_ajukan_kemenkeu.required' => 'Tanggal pengajuan ke Kemenkeu wajib diisi.',
            'no_spm.required' => 'Nomor SPM wajib diisi.',
            'tgl_spm.required' => 'Tanggal SPM wajib diisi.',
        ]);

        $this->pencairanService->ppspmAjukanKemenkeu($pengajuan, $data);

        return redirect()->route('ppspm.pengajuan.index')->with('success', 'Diajukan ke Kemenkeu.');
    }

    public function riwayat()
    {
        $riwayat = Ppspm::where('id_user', auth()->id())
            ->with(['pengajuan.user', 'pengajuan.status'])
            ->orderByDesc('id_ppspm')
            ->paginate(15);
        return view('ppspm.riwayat', compact('riwayat'));
    }
}