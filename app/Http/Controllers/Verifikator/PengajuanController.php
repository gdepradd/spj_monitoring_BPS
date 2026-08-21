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
            'status',

            'verifikasi' => function ($query) {
                $query->orderBy('tahap');
            },

            'verifikasi.verifikator',
            'verifikasi.statusVerifikasi',
        ])->findOrFail($id);

        $urutan = (int) auth()->user()->urutan_verifikator;

        $kodeStatusYangDiizinkan = match ($urutan) {
            1 => ['DIAJUKAN', 'VERIFIKASI_1'],
            2 => ['VERIFIKASI_2'],
            3 => ['VERIFIKASI_3'],
            default => [],
        };

        if (!in_array(
            $pengajuan->status?->kode_status,
            $kodeStatusYangDiizinkan,
            true
        )) {
            abort(
                403,
                'Pengajuan ini tidak berada pada tahap verifikasi Anda.'
            );
        }

        return view(
            'verifikator.pengajuan.show',
            compact('pengajuan')
        );
    }

    public function keputusan(Request $request, $id)
{
    $pengajuan = Pengajuan::findOrFail($id);

    $tahapVerifikator = (int) auth()->user()->urutan_verifikator;

    /*
    |--------------------------------------------------------------------------
    | Pastikan pengajuan memang sedang berada pada tahap verifikator ini
    |--------------------------------------------------------------------------
    */
    $kodeStatusYangDiizinkan = match ($tahapVerifikator) {
        1 => ['DIAJUKAN', 'VERIFIKASI_1'],
        2 => ['VERIFIKASI_2'],
        3 => ['VERIFIKASI_3'],
        default => [],
    };

    $pengajuan->load('status');

    if (!in_array(
        $pengajuan->status?->kode_status,
        $kodeStatusYangDiizinkan,
        true
    )) {
        abort(
            403,
            'Pengajuan ini tidak berada pada tahap verifikasi Anda.'
        );
    }

    $request->validate([
        'id_status_verifikasi' => [
            'required',
            'exists:status_verifikasi,id_status_verifikasi',
        ],

        /*
         * 3 = REVISI
         * 4 = TOLAK
         */
        'catatan' => [
            'nullable',
            'required_if:id_status_verifikasi,3,4',
        ],
    ]);

    $this->verifikasiService->prosesKeputusan(
        $pengajuan,
        $request->only([
            'id_status_verifikasi',
            'catatan',
        ]),
        $tahapVerifikator
    );

    return redirect()
        ->route('verifikator.pengajuan.index')
        ->with(
            'success',
            'Keputusan verifikasi berhasil disimpan.'
        );
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