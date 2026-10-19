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
        $pengajuan = Pengajuan::with(['user', 'status'])->where('id_status', $idPpk)->get();
        return view('ppk.pengajuan.index', compact('pengajuan'));
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
            // Daftar penerima usulan pengaju, ditampilkan di layar ini.
            'penerima',
            'ppspm.user',
        ])->findOrFail($id);

        return view('ppk.pengajuan.show', compact('pengajuan'));
    }

    public function terbitkanSpm(Request $request, $id)
    {
        $pengajuan = Pengajuan::with('status')->findOrFail($id);

        // Hanya boleh diproses saat pengajuan benar-benar berada di tahap PPK.
        if ($pengajuan->status?->kode_status !== 'PROSES_PPK') {
            return redirect()
                ->route('ppk.pengajuan.index')
                ->with('error', 'Pengajuan ini tidak berada pada tahap PPK.');
        }

        // SPP dari Bendahara wajib ada sebelum PPK menyetujui.
        $spp = $pengajuan->bendahara()
            ->where('tahap', 'PENGAJUAN_SPP')
            ->orderByDesc('id_bendahara')
            ->first();

        if (! $spp || ! $spp->no_spp) {
            return back()->with('error', 'Dokumen SPP dari Bendahara belum lengkap. Persetujuan belum dapat dilakukan.');
        }

        $data = $request->validate([
            'catatan' => 'nullable|string|max:5000',
        ]);

        $this->pencairanService->ppkTerbitkanSpm($pengajuan, $data);

        return redirect()->route('ppk.pengajuan.index')->with('success', 'SPP disetujui dan diteruskan ke PPSPM.');
    }

    public function riwayat()
    {
        $riwayat = Ppk::where('id_user', auth()->id())
            // bendahara ikut dimuat supaya nomor SPP dapat ditampilkan tanpa query per baris.
            ->with(['pengajuan.user', 'pengajuan.status', 'pengajuan.bendahara'])
            ->orderByDesc('id_ppk')
            ->paginate(15);
        return view('ppk.riwayat', compact('riwayat'));
    }
}