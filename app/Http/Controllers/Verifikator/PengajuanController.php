<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\Verifikasi;
use App\Services\VerifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanController extends Controller
{
    protected $verifikasiService;

    public function __construct(VerifikasiService $verifikasiService)
    {
        $this->verifikasiService = $verifikasiService;
    }

    public function index()
    {
        $urutan = auth()->user()->urutan_verifikator;

        $query = Pengajuan::with('pemohon');

        if ($urutan == 1) {
            $query->whereIn('id_status', [1, 2]);
        } elseif ($urutan == 2) {
            $query->where('id_status', 3);
        } elseif ($urutan == 3) {
            $query->where('id_status', 4);
        }

        $pengajuan = $query->get();

        return view('verifikator.pengajuan.index', compact('pengajuan'));
    }

    public function show($id)
{
    $pengajuan = Pengajuan::with([
        'pemohon',
        'verifikasi.verifikator',
        'verifikasi.statusVerifikasi'
    ])->findOrFail($id);

    $urutan = auth()->user()->urutan_verifikator;

    $allowedStatus = match ($urutan) {
        1 => [1, 2],
        2 => [3],
        3 => [4],
        default => [],
    };

    if (! in_array($pengajuan->id_status, $allowedStatus)) {
        abort(403, 'Pengajuan ini tidak berada pada tahap verifikasi Anda.');
    }

    $timeline = collect([
        [
            'waktu' => $pengajuan->created_at,
            'judul' => 'Pengajuan dibuat',
            'nama_status' => 'Diajukan',
            'catatan' => $pengajuan->catatan_pengaju,
            'aktor' => $pengajuan->pemohon?->nama_lengkap ?? 'Pegawai',
        ],
    ]);

    $verifikasi = DB::table('verifikasi as v')
        ->join(
            'status_verifikasi as s',
            's.id_status_verifikasi',
            '=',
            'v.id_status_verifikasi'
        )
        ->join(
            'users as u',
            'u.id_user',
            '=',
            'v.id_verifikator'
        )
        ->where('v.id_pengajuan', $pengajuan->id_pengajuan)
        ->select([
            'v.tanggal_verifikasi as waktu',
            'v.tahap',
            'v.catatan',
            's.kode_status',
            's.nama_status',
            'u.nama_lengkap as aktor',
        ])
        ->get()
        ->map(fn ($item) => [
            'waktu' => $item->waktu,
            'judul' => 'Verifikasi Tahap ' . $item->tahap,
            'nama_status' => $item->nama_status,
            'catatan' => $item->catatan,
            'aktor' => $item->aktor,
        ]);

    $timeline = $timeline
        ->concat($verifikasi)
        ->sortBy('waktu')
        ->values();

    return view(
        'verifikator.pengajuan.show',
        compact('pengajuan', 'timeline')
    );
}

    public function keputusan(Request $request, $id)
    {
        $request->validate([
            'id_status_verifikasi' => 'required|exists:status_verifikasi,id_status_verifikasi',
            'catatan' => 'required_if:id_status_verifikasi,2,3', // 2: REVISI, 3: TOLAK (Sesuaikan ID-nya)
        ]);

        $pengajuan = Pengajuan::findOrFail($id);
        $tahapVerifikator = auth()->user()->urutan_verifikator;

        $this->verifikasiService->prosesKeputusan($pengajuan, $request->all(), $tahapVerifikator);

        return redirect()->route('verifikator.pengajuan.index')->with('success', 'Keputusan berhasil disimpan.');
    }

    public function riwayat()
    {
        $riwayat = Verifikasi::with(['pengajuan', 'statusVerifikasi'])
                    ->where('id_verifikator', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('verifikator.riwayat', compact('riwayat'));
    }
}