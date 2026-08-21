<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\StorePengajuanRequest;
use App\Http\Requests\Pegawai\UpdatePengajuanRequest;
use App\Models\Pengajuan;
use App\Models\StatusPengajuan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PengajuanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Daftar Pengajuan Pegawai
    |--------------------------------------------------------------------------
    */
    public function index()
{
    $urutan = (int) auth()->user()->urutan_verifikator;

    $kodeStatusYangDiizinkan = match ($urutan) {
        1 => ['DIAJUKAN', 'VERIFIKASI_1'],
        2 => ['VERIFIKASI_2'],
        3 => ['VERIFIKASI_3'],
        default => [],
    };

    $pengajuan = Pengajuan::query()
        ->with([
            'pemohon',
            'status',
        ])
        ->whereHas('status', function ($query) use ($kodeStatusYangDiizinkan) {
            $query->whereIn(
                'kode_status',
                $kodeStatusYangDiizinkan
            );
        })
        ->orderBy('tanggal_pengajuan', 'asc')
        ->get();

    return view(
        'verifikator.pengajuan.index',
        compact('pengajuan', 'urutan')
    );
}


    /*
    |--------------------------------------------------------------------------
    | Form Pengajuan Baru
    |--------------------------------------------------------------------------
    */
    public function create(): View
    {
        return view('pegawai.pengajuan.create');
    }


    /*
    |--------------------------------------------------------------------------
    | Simpan Pengajuan Baru
    |--------------------------------------------------------------------------
    */
    public function store(
        StorePengajuanRequest $request
    ): RedirectResponse {
        $statusDiajukan = StatusPengajuan::query()
            ->where('kode_status', 'DIAJUKAN')
            ->firstOrFail();

        $pengajuan = DB::transaction(
            function () use ($request, $statusDiajukan) {

                return Pengajuan::create([
                    'no_pengajuan' => $this->generateNoPengajuan(
                        $request
                            ->date('tanggal_pengajuan')
                            ->format('Ymd')
                    ),

                    'id_user' => Auth::id(),

                    'tanggal_pengajuan' =>
                        $request->date('tanggal_pengajuan'),

                    'perihal' =>
                        $request->string('perihal')->toString(),

                    'keterangan' =>
                        $request->string('keterangan')->toString(),

                    'total_nominal' =>
                        $request->input('total_nominal'),

                    'id_status' =>
                        $statusDiajukan->id_status,

                    // Metode baru dipilih Bendahara.
                    'metode_pembayaran' => null,

                    'catatan_pengaju' => null,
                ]);
            }
        );

        return redirect()
            ->route(
                'pegawai.pengajuan.show',
                $pengajuan
            )
            ->with(
                'success',
                'Pengajuan berhasil dibuat dan menunggu proses verifikasi.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Detail Pengajuan
    |--------------------------------------------------------------------------
    */
    public function show(Pengajuan $pengajuan): View
    {
        /*
         * Authorization dilakukan terlebih dahulu supaya
         * pengguna lain tidak dapat membaca detail pengajuan.
         */
        Gate::authorize('view', $pengajuan);

        /*
         * Seluruh data timeline dimuat melalui relationship.
         *
         * Timeline tidak lagi dibuat dengan query manual karena
         * setiap tahap harus menggunakan status record-nya sendiri.
         */
        $pengajuan->load([
            'status',
            'pemohon',

            /*
            |--------------------------------------------------------------------------
            | VERIFIKASI
            |--------------------------------------------------------------------------
            */
            'verifikasi' => function ($query) {
                $query
                    ->orderBy('tahap')
                    ->orderBy('id_verifikasi');
            },

            'verifikasi.verifikator',
            'verifikasi.statusVerifikasi',

            /*
            |--------------------------------------------------------------------------
            | BENDAHARA
            |--------------------------------------------------------------------------
            |
            | Bendahara sekarang dapat mempunyai beberapa record:
            |
            | - PENGAJUAN_SPP
            | - PEMBAYARAN_LANGSUNG
            | - KONFIRMASI
            |
            */
            'bendahara' => function ($query) {
                $query->orderBy('id_bendahara');
            },

            'bendahara.user',
            'bendahara.statusPencairan',

            /*
            |--------------------------------------------------------------------------
            | PPK
            |--------------------------------------------------------------------------
            */
            'ppk' => function ($query) {
                $query->orderBy('id_ppk');
            },

            'ppk.user',
            'ppk.statusPencairan',

            /*
            |--------------------------------------------------------------------------
            | PPSPM
            |--------------------------------------------------------------------------
            */
            'ppspm' => function ($query) {
                $query->orderBy('id_ppspm');
            },

            'ppspm.user',
            'ppspm.statusPencairan',
        ]);

        return view(
            'pegawai.pengajuan.show',
            compact('pengajuan')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Form Revisi Pengajuan
    |--------------------------------------------------------------------------
    */
    public function edit(Pengajuan $pengajuan): View
    {
        $pengajuan->load('status');

        Gate::authorize('update', $pengajuan);

        return view(
            'pegawai.pengajuan.edit',
            compact('pengajuan')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Simpan Perbaikan / Revisi
    |--------------------------------------------------------------------------
    */
    public function update(
        UpdatePengajuanRequest $request,
        Pengajuan $pengajuan
    ): RedirectResponse {
        $pengajuan->load('status');

        Gate::authorize('update', $pengajuan);

        /*
         * Setelah pegawai memperbaiki pengajuan,
         * proses kembali ke Verifikator 1.
         */
        $statusVerifikasiSatu = StatusPengajuan::query()
            ->where('kode_status', 'VERIFIKASI_1')
            ->firstOrFail();

        DB::transaction(
            function () use (
                $request,
                $pengajuan,
                $statusVerifikasiSatu
            ) {
                $pengajuan->update([
                    'tanggal_pengajuan' =>
                        $request->date('tanggal_pengajuan'),

                    'perihal' =>
                        $request->string('perihal')->toString(),

                    'keterangan' =>
                        $request->string('keterangan')->toString(),

                    'total_nominal' =>
                        $request->input('total_nominal'),

                    'catatan_pengaju' =>
                        $request->filled('catatan_pengaju')
                            ? $request
                                ->string('catatan_pengaju')
                                ->toString()
                            : null,

                    'id_status' =>
                        $statusVerifikasiSatu->id_status,
                ]);
            }
        );

        return redirect()
            ->route(
                'pegawai.pengajuan.show',
                $pengajuan
            )
            ->with(
                'success',
                'Perbaikan berhasil dikirim. Pengajuan kembali ke Verifikator 1.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Riwayat Pengajuan
    |--------------------------------------------------------------------------
    */
    public function riwayat(): View
    {
        $pengajuan = Pengajuan::query()
            ->with('status')
            ->where('id_user', Auth::id())
            ->latest('created_at')
            ->paginate(15);

        return view(
            'pegawai.pengajuan.riwayat',
            compact('pengajuan')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Nomor Pengajuan
    |--------------------------------------------------------------------------
    */
    private function generateNoPengajuan(
        string $tanggal
    ): string {
        $prefix = "PGJ-{$tanggal}-";

        $latest = Pengajuan::query()
            ->where(
                'no_pengajuan',
                'like',
                $prefix . '%'
            )
            ->orderByDesc('no_pengajuan')
            ->lockForUpdate()
            ->value('no_pengajuan');

        $sequence = $latest
            ? ((int) substr($latest, -4)) + 1
            : 1;

        if ($sequence > 9999) {
            throw ValidationException::withMessages([
                'tanggal_pengajuan' =>
                    'Nomor pengajuan untuk tanggal tersebut sudah mencapai batas harian.',
            ]);
        }

        return $prefix .
            str_pad(
                (string) $sequence,
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}