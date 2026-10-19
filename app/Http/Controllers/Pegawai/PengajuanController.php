<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\StorePengajuanRequest;
use App\Http\Requests\Pegawai\UpdatePengajuanRequest;
use App\Models\Pengajuan;
use App\Models\StatusPengajuan;
use App\Models\StatusVerifikasi;
use App\Models\Verifikasi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use App\Services\FonnteService;
class PengajuanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Daftar Pengajuan Pegawai
    |--------------------------------------------------------------------------
    */
    /**
     * Kolom yang boleh dipakai untuk mengurutkan daftar pengajuan.
     * Kuncinya adalah nilai ?urut= pada URL, isinya kolom tabel yang dipakai
     * di ORDER BY. Daftar putih ini sekaligus mencegah nilai sembarang dari
     * URL masuk ke query.
     */
    private const KOLOM_URUT = [
        'tanggal_pengajuan' => 'pengajuan.tanggal_pengajuan',
        'total_nominal' => 'pengajuan.total_nominal',
        // tanggal_selesai bukan kolom tabel melainkan accessor, jadi
        // pengurutannya ditangani urutkanTanggalSelesai().
        'tanggal_selesai' => null,
    ];

    public function index(): View
    {
        $urut = array_key_exists((string) request('urut'), self::KOLOM_URUT)
            ? (string) request('urut')
            : 'tanggal_pengajuan';

        $arah = request('arah') === 'asc' ? 'asc' : 'desc';

        $query = Pengajuan::query()
            // bendahara ikut dimuat agar tanggal selesai dan total yang sudah
            // dibayar tidak memicu query per baris.
            ->with(['status', 'bendahara'])
            ->where('id_user', Auth::id());

        if ($kodeStatus = request('status')) {
            $query->whereHas(
                'status',
                fn ($q) => $q->where(
                    'kode_status',
                    $kodeStatus
                )
            );
        }

        if ($urut === 'tanggal_selesai') {
            $this->urutkanTanggalSelesai($query, $arah);
        } else {
            $query->orderBy(self::KOLOM_URUT[$urut], $arah);
        }

        // Pengurut kedua: menjaga urutan baris tetap sama antar halaman bila
        // nilai kolom utamanya seri (mis. beberapa pengajuan di tanggal sama).
        $query->orderByDesc('pengajuan.id_pengajuan');

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
                'statusList',
                'urut',
                'arah'
            )
        );
    }


    /**
     * Urutkan berdasarkan tanggal selesai.
     *
     * Tanggal selesai tidak disimpan sebagai kolom; nilainya berasal dari
     * pembayaran terakhir dan hanya berlaku saat pengajuan sudah SELESAI.
     * Ekspresi di bawah menirukan accessor getTanggalSelesaiAttribute() supaya
     * urutan yang muncul sama dengan angka yang dibaca pengguna di tabel.
     * Pengajuan yang belum selesai bernilai NULL sehingga mengelompok di ujung.
     */
    private function urutkanTanggalSelesai($query, string $arah): void
    {
        $idSelesai = (int) StatusPengajuan::query()
            ->where('kode_status', 'SELESAI')
            ->value('id_status');

        // $arah sudah dibatasi pada 'asc'/'desc' oleh index(), dan $idSelesai
        // dikirim sebagai binding, jadi tidak ada nilai dari URL yang masuk
        // ke SQL secara langsung.
        $query->orderByRaw(
            '(case when pengajuan.id_status = ?'
            . ' then ('
            . '   select max(coalesce(b.tgl_transfer, b.tanggal_proses))'
            . '   from bendahara b'
            . '   where b.id_pengajuan = pengajuan.id_pengajuan'
            . "     and b.tahap in ('PEMBAYARAN_LANGSUNG', 'KONFIRMASI')"
            . ' ) end) ' . $arah,
            [$idSelesai]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Form Pengajuan Baru
    |--------------------------------------------------------------------------
    */
    public function create(): View
    {
        return view('pegawai.pengajuan.create', [
            'divisiPengaju' => $this->divisiPengaju(),
        ]);
    }


    /**
     * Nama divisi akun yang sedang login, dipakai sebagai tim pengaju.
     * Mengembalikan null bila admin belum menetapkan divisi untuk akun ini.
     */
    private function divisiPengaju(): ?string
    {
        return auth()->user()?->divisi?->nama_divisi;
    }


    /*
    |--------------------------------------------------------------------------
    | Simpan Pengajuan Baru
    |--------------------------------------------------------------------------
    */
    public function store(StorePengajuanRequest $request): RedirectResponse
    {
        $pengaju = auth()->user();

        /*
         * Verifikator tahap 1 yang mengajukan SPJ-nya sendiri tidak boleh
         * memverifikasi pengajuannya sendiri. Di instansi ini hanya ada satu
         * verifikator per tahap, jadi pengajuannya tidak bisa sekadar
         * dikeluarkan dari antrean — tidak ada yang menggantikan dan pengajuan
         * akan macet selamanya. Karena itu tahap 1 dilewati: pengajuan langsung
         * mulai di VERIFIKASI_2, dan persetujuan tahap 1 dicatat atas nama
         * pengaju itu sendiri supaya riwayatnya tetap utuh (tidak ada tahap
         * yang bolong). Perlakuan ini KHUSUS verifikator tahap 1; tahap 2, 3
         * dan seluruh peran pencairan tetap melalui alur normal.
         */
        $lewatiTahap1 = $pengaju->isRole('verifikator')
            && (int) $pengaju->urutan_verifikator === 1;

        $kodeStatusAwal = $lewatiTahap1 ? 'VERIFIKASI_2' : 'DIAJUKAN';

        $statusAwal = StatusPengajuan::query()
            ->where('kode_status', $kodeStatusAwal)
            ->firstOrFail();

        $pengajuan = DB::transaction(function () use ($request, $statusAwal, $lewatiTahap1, $pengaju) {
            $pengajuan = Pengajuan::create([
                'no_pengajuan' => $this->generateNoPengajuan(
                    \Carbon\Carbon::parse($request->tanggal_pengajuan)->format('Ymd')
                ),
                'id_user' => $pengaju->id_user,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'perihal' => \Illuminate\Support\Str::limit($request->keterangan, 100, '...'),
                'keterangan' => $request->keterangan,
                'total_nominal' => $request->total_nominal,
                'id_status' => $statusAwal->id_status,
                'bulan_kegiatan' => $request->bulan_kegiatan,
                // Bukan dari input: tim pengaju selalu mengikuti divisi akun.
                'tim_pengaju' => $this->divisiPengaju(),
                'nama_penerima' => $request->nama_penerima,
                'catatan_pengaju' => $request->catatan_pengaju,
            ]);
            \App\Services\FonnteService::notifyRole('verifikator', "*PENGAJUAN BARU*\nAda pengajuan baru dari {$pengaju->name}.\nPerihal: {$pengajuan->perihal}\nNominal: Rp " . number_format($pengajuan->total_nominal, 0, ',', '.') . "\nMohon untuk segera diverifikasi.");

            if ($lewatiTahap1) {
                $statusAcc = StatusVerifikasi::query()
                    ->where('kode_status', 'ACC')
                    ->firstOrFail();

                Verifikasi::create([
                    'id_pengajuan' => $pengajuan->id_pengajuan,
                    'id_verifikator' => $pengaju->id_user,
                    'tahap' => 1,
                    'tanggal_verifikasi' => now(),
                    'id_status_verifikasi' => $statusAcc->id_status_verifikasi,
                    'catatan' => 'Disetujui otomatis: pengajuan ini dibuat oleh verifikator tahap 1 sendiri, sehingga verifikasi tahap 1 dilewati.',
                ]);
            }

            return $pengajuan;
        });

        $pesan = $lewatiTahap1
            ? 'Pengajuan berhasil dibuat. Karena Anda verifikator tahap 1, verifikasi tahap 1 tercatat otomatis dan pengajuan langsung diteruskan ke verifikator tahap 2.'
            : 'Pengajuan berhasil dibuat.';

        return redirect()->route('pengajuan.index')->with('success', $pesan);
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
            compact('pengajuan') + ['divisiPengaju' => $this->divisiPengaju()]
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
            // Mengikuti divisi akun; nilai lama dipertahankan bila belum ada divisi
            // supaya data pengajuan lama tidak terhapus saat revisi.
            'tim_pengaju'       => $this->divisiPengaju() ?? $pengajuan->tim_pengaju,
            'nama_penerima'     => $request->nama_penerima,
            'catatan_pengaju'   => $request->catatan_pengaju,
            'id_status'         => $statusTarget->id_status, // Status dinamis masuk ke sini
        ]);

        FonnteService::notifyRole(
            'verifikator',
            "*REVISI PENGAJUAN DIKIRIM*\nPengajuan '{$pengajuan->perihal}' dari " . auth()->user()->nama_lengkap . " telah direvisi dan dikirim kembali untuk ditinjau ulang."
        );

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan berhasil diperbaiki dan dikembalikan ke pemeriksa terakhir.');
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