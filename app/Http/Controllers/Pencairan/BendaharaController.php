<?php

namespace App\Http\Controllers\Pencairan;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\Bendahara;
use App\Models\StatusPengajuan;
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
        $idMenunggu = StatusPengajuan::where('kode_status', 'MENUNGGU_PENCAIRAN')->value('id_status');
        $idKonfirmasi = StatusPengajuan::where('kode_status', 'PROSES_KONFIRMASI_BENDAHARA')->value('id_status');
        
        $totalMenunggu = Pengajuan::whereIn('id_status', [$idMenunggu, $idKonfirmasi])->count();
        
        return view('bendahara.dashboard', compact('totalMenunggu'));
    }

    public function index()
    {
        $idMenunggu = StatusPengajuan::where('kode_status', 'MENUNGGU_PENCAIRAN')->value('id_status');
        $idKonfirmasi = StatusPengajuan::where('kode_status', 'PROSES_KONFIRMASI_BENDAHARA')->value('id_status');

        // Pisahkan menjadi dua variabel untuk 2 tab/section di antarmuka
        $perluDiajukan = Pengajuan::with(['user', 'status'])
            ->where('id_status', $idMenunggu)
            ->orderBy('tanggal_pengajuan')
            ->get();

        $perluDikonfirmasi = Pengajuan::with(['user', 'status'])
            ->where('id_status', $idKonfirmasi)
            ->orderBy('tanggal_pengajuan')
            ->get();

        return view('bendahara.pengajuan.index', compact('perluDiajukan', 'perluDikonfirmasi'));
    }

    public function show($id)
    {
        $pengajuan = Pengajuan::with([
            'user', // Menggunakan 'user' sesuai pembaruan dari Dev 1
            'status',
            'verifikasi' => function ($query) {
                $query->orderBy('tahap');
            },
            'verifikasi.verifikator',
            'verifikasi.statusVerifikasi',
        ])->findOrFail($id);

        $idMenunggu = StatusPengajuan::where('kode_status', 'MENUNGGU_PENCAIRAN')->value('id_status');
        $idKonfirmasi = StatusPengajuan::where('kode_status', 'PROSES_KONFIRMASI_BENDAHARA')->value('id_status');

        if (!in_array($pengajuan->id_status, [$idMenunggu, $idKonfirmasi])) {
            abort(403, 'Bukan wewenang Bendahara pada tahap ini.');
        }

        return view('bendahara.pengajuan.show', compact('pengajuan'));
    }

    public function ajukan(Request $request, $id)
    {
        $request->validate([
            'id_status_pencairan' => 'required|in:1,2,3', // 1=ACC, 2=REVISI, 3=TOLAK
            'metode_pembayaran' => 'required_if:id_status_pencairan,1',
            'catatan' => 'required_if:id_status_pencairan,2,3' // Wajib jika ditolak/revisi
        ]);

        $pengajuan = Pengajuan::findOrFail($id);

        // Jika REVISI (2) atau TOLAK (3)
        if ($request->id_status_pencairan != 1) {
            $this->pencairanService->tolakAtauRevisiBendahara($pengajuan, $request->all(), 'PENGAJUAN_SPP');
            return redirect()->route('bendahara.pengajuan.index')->with('success', 'Keputusan revisi/tolak berhasil disimpan.');
        }
        
        // Jika ACC (1)
        $this->pencairanService->pilihMetodePembayaran($pengajuan, $request->metode_pembayaran);

        if ($request->metode_pembayaran === 'UP_TUP') {
            $this->pencairanService->bendaharaBayarLangsung($pengajuan, $request->all());
        } else {
            $this->pencairanService->bendaharaAjukanSpp($pengajuan, $request->all());
        }

        return redirect()->route('bendahara.pengajuan.index')->with('success', 'Metode & Pengajuan berhasil diproses.');
    }

    public function konfirmasi(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        
        // Validasi wajib isi untuk tahap akhir LS_PIHAK_KETIGA
        if ($pengajuan->metode_pembayaran === 'LS_PIHAK_KETIGA') {
            $request->validate([
                'no_spm' => 'required',
                'tgl_transfer' => 'required|date',
                'no_sp2d' => 'required',
                'tgl_sp2d' => 'required|date',
            ]);
        } else {
            // Untuk UP_TUP dan LS_BENDAHARA
            $request->validate(['tgl_transfer' => 'required|date']);
        }

        $this->pencairanService->bendaharaKonfirmasi($pengajuan, $request->all());

        return redirect()->route('bendahara.pengajuan.index')->with('success', 'Konfirmasi pencairan selesai.');
    }

    public function riwayat()
    {
        $riwayat = Bendahara::where('id_user', auth()->id())
                    ->with('pengajuan.user')
                    ->latest()
                    ->paginate(15);
                    
        return view('bendahara.riwayat', compact('riwayat'));
    }
}