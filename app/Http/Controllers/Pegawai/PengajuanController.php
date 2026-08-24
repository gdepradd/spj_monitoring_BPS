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
    public function index(): View
    {
        $query = Pengajuan::query()
            ->with('status')
            ->where('id_user', Auth::id())
            ->latest('created_at');

        if ($kodeStatus = request('status')) {
            $query->whereHas(
                'status',
                fn ($q) => $q->where(
                    'kode_status',
                    $kodeStatus
                )
            );
        }

        $pengajuan = $query
            ->paginate(10)
            ->withQueryString();

        $statusList = StatusPengajuan::query()
            ->orderBy('urutan')
            ->get();

        return view(
            'pegawai.pengajuan.index',
            compact(
                'pengajuan',
                'statusList'
            )
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
    public function store(StorePengajuanRequest $request): RedirectResponse
    {
        $statusDiajukan = StatusPengajuan::query()
            ->where('kode_status', 'DIAJUKAN')
            ->firstOrFail();

        $pengajuan = DB::transaction(function () use ($request, $statusDiajukan) {
            return Pengajuan::create([
                'no_pengajuan' => $this->generateNoPengajuan(
                    \Carbon\Carbon::parse($request->tanggal_pengajuan)->format('Ymd')
                ),
                'id_user' => auth()->id(),
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'perihal' => \Illuminate\Support\Str::limit($request->keterangan, 100, '...'),
                'keterangan' => $request->keterangan,
                'total_nominal' => $request->total_nominal,
                'id_status' => $statusDiajukan->id_status,
                'bulan_kegiatan' => $request->bulan_kegiatan,
                'tim_pengaju' => $request->tim_pengaju,
                'nama_penerima' => $request->nama_penerima,
                'catatan_pengaju' => $request->catatan_pengaju,
            ]);
        });

        return redirect()->route('pegawai.pengajuan.index')->with('success', 'Pengajuan berhasil dibuat.');
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
   public function update(UpdatePengajuanRequest $request, Pengajuan $pengajuan)
    {
        // 1. Cari riwayat verifikasi terakhir untuk pengajuan ini
        $verifikasiTerakhir = $pengajuan->verifikasi()->latest('id_verifikasi')->first();

        // 2. Tentukan kode status kembalian berdasarkan tahap terakhir
        if ($verifikasiTerakhir) {
            // Jika yang merevisi Verifikator 3, maka status kembali ke VERIFIKASI_3, dst.
            $kodeStatusBaru = 'VERIFIKASI_' . $verifikasiTerakhir->tahap;
        } else {
            // Jika belum pernah diverifikasi sama sekali, kembali ke DIAJUKAN
            $kodeStatusBaru = 'DIAJUKAN';
        }

        // 3. Ambil id_status dari tabel status pengajuan
        $statusTarget = StatusPengajuan::where('kode_status', $kodeStatusBaru)->firstOrFail();

        // 4. Update data pengajuan
        $pengajuan->update([
            'perihal'           => \Illuminate\Support\Str::limit($request->keterangan, 100, '...'),
            'keterangan'        => $request->keterangan,
            'total_nominal'     => $request->total_nominal,
            'bulan_kegiatan'    => $request->bulan_kegiatan,
            'tim_pengaju'       => $request->tim_pengaju,
            'nama_penerima'     => $request->nama_penerima,
            'id_status'         => $statusTarget->id_status, // Status dinamis masuk ke sini
        ]);

        return redirect()->route('pegawai.pengajuan.index')->with('success', 'Pengajuan berhasil diperbaiki dan dikembalikan ke pemeriksa terakhir.');
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