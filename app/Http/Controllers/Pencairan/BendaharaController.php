<?php

namespace App\Http\Controllers\Pencairan;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\Bendahara;
use App\Models\StatusPengajuan;
use App\Services\PencairanService;
use App\Support\Rupiah;
use Illuminate\Http\Request;

class BendaharaController extends Controller
{
    protected $pencairanService;

    public function __construct(PencairanService $pencairanService)
    {
        $this->pencairanService = $pencairanService;
    }

    public function dashboard(Request $request)
    {
        $filter = $request->validate([
            'metode' => ['nullable', 'in:LS_BENDAHARA,LS_PIHAK_KETIGA,UP_TUP'],
        ]);

        $metode = $filter['metode'] ?? null;

        $idMenunggu = StatusPengajuan::where('kode_status', 'MENUNGGU_PENCAIRAN')->value('id_status');
        $idKonfirmasi = StatusPengajuan::where('kode_status', 'PROSES_KONFIRMASI_BENDAHARA')->value('id_status');
        $idParsial = StatusPengajuan::where('kode_status', 'PENCAIRAN_PARSIAL')->value('id_status');

        $totalMenunggu = Pengajuan::whereIn('id_status', [$idMenunggu, $idKonfirmasi])->count();

        // Basis resume: pengajuan yang sudah di-ACC Bendahara (metode sudah dipilih).
        $basis = Pengajuan::whereNotNull('metode_pembayaran')
            ->when($metode, fn ($q) => $q->where('metode_pembayaran', $metode));

        $nominalAcc = (float) (clone $basis)->sum('total_nominal');
        $jumlahAcc = (clone $basis)->count();

        $nominalCair = (float) Bendahara::whereIn('tahap', ['PEMBAYARAN_LANGSUNG', 'KONFIRMASI'])
            ->when($metode, fn ($q) => $q->whereHas('pengajuan', fn ($p) => $p->where('metode_pembayaran', $metode)))
            ->sum('nominal');

        $nominalBelumCair = max(0, $nominalAcc - $nominalCair);

        // Rincian per metode pembayaran, dipakai sebagai opsi filter sekaligus ringkasan.
        $perMetode = Pengajuan::query()
            ->whereNotNull('metode_pembayaran')
            ->selectRaw('metode_pembayaran, COUNT(*) as jumlah, SUM(total_nominal) as nominal')
            ->groupBy('metode_pembayaran')
            ->get()
            ->map(function ($baris) {
                $baris->cair = (float) Bendahara::whereIn('tahap', ['PEMBAYARAN_LANGSUNG', 'KONFIRMASI'])
                    ->whereHas('pengajuan', fn ($p) => $p->where('metode_pembayaran', $baris->metode_pembayaran))
                    ->sum('nominal');

                return $baris;
            });

        // Pengajuan yang pembayarannya masih bersisa, agar mudah ditindaklanjuti.
        $pencairanParsial = Pengajuan::with(['user', 'status', 'bendahara'])
            ->where('id_status', $idParsial)
            ->when($metode, fn ($q) => $q->where('metode_pembayaran', $metode))
            ->orderBy('tanggal_pengajuan')
            ->get();

        return view('bendahara.dashboard', compact(
            'totalMenunggu',
            'nominalAcc',
            'jumlahAcc',
            'nominalCair',
            'nominalBelumCair',
            'perMetode',
            'pencairanParsial',
            'metode'
        ));
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

        // Pengajuan yang dicairkan bertahap dan masih menyisakan pembayaran.
        $idParsial = StatusPengajuan::where('kode_status', 'PENCAIRAN_PARSIAL')->value('id_status');

        $pencairanParsial = Pengajuan::with(['user', 'status', 'bendahara'])
            ->where('id_status', $idParsial)
            ->orderBy('tanggal_pengajuan')
            ->get();

        return view('bendahara.pengajuan.index', compact('perluDiajukan', 'perluDikonfirmasi', 'pencairanParsial'));
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
            'bendahara.user',
            'ppk.user',
            // Daftar penerima usulan pengaju, ditampilkan di layar ini.
            'penerima',
            'ppspm.user',
        ])->findOrFail($id);

        $idMenunggu = StatusPengajuan::where('kode_status', 'MENUNGGU_PENCAIRAN')->value('id_status');
        $idKonfirmasi = StatusPengajuan::where('kode_status', 'PROSES_KONFIRMASI_BENDAHARA')->value('id_status');
        $idParsial = StatusPengajuan::where('kode_status', 'PENCAIRAN_PARSIAL')->value('id_status');

        if (!in_array($pengajuan->id_status, [$idMenunggu, $idKonfirmasi, $idParsial])) {
            abort(403, 'Bukan wewenang Bendahara pada tahap ini.');
        }

        return view('bendahara.pengajuan.show', compact('pengajuan'));
    }

    public function ajukan(Request $request, $id)
    {
        $pengajuan = Pengajuan::with('status')->findOrFail($id);

        // Cegah pengajuan diproses ulang dari tahap yang sudah lewat.
        if ($pengajuan->status?->kode_status !== 'MENUNGGU_PENCAIRAN') {
            return redirect()
                ->route('bendahara.pengajuan.index')
                ->with('error', 'Pengajuan ini tidak berada pada tahap pemilihan metode pembayaran.');
        }

        $this->normalkanNominalPenerima($request);

        $request->validate(array_merge([
            'id_status_pencairan' => 'required|in:1,2,3', // 1=ACC, 2=REVISI, 3=TOLAK
            'metode_pembayaran' => 'required_if:id_status_pencairan,1|nullable|in:LS_BENDAHARA,LS_PIHAK_KETIGA,UP_TUP',
            // Dokumen SPP wajib pada metode LS agar bisa dibaca PPK, PPSPM, dan saat konfirmasi.
            'no_spp' => 'required_if:metode_pembayaran,LS_BENDAHARA,LS_PIHAK_KETIGA',
            'tgl_spp' => 'required_if:metode_pembayaran,LS_BENDAHARA,LS_PIHAK_KETIGA|nullable|date',
            // UP/TUP selesai di tangan Bendahara: bisa transfer atau tunai,
            // dibayar penuh sekaligus atau bertahap ke beberapa penerima.
            'jenis_pencairan' => 'required_if:metode_pembayaran,UP_TUP|nullable|in:PENUH,PARSIAL',
            'tgl_transfer' => 'required_if:metode_pembayaran,UP_TUP|nullable|date',
            // Cara bayar & kwitansi berlaku untuk seluruh penerima pada satu pembayaran.
            'cara_bayar' => 'required_if:metode_pembayaran,UP_TUP|nullable|in:TRANSFER,TUNAI',
            'no_kwitansi' => 'required_if:cara_bayar,TUNAI|nullable|string|max:100',
            // Nama penerima tunggal hanya dipakai pada pencairan penuh.
            'nama_penerima' => 'exclude_unless:jenis_pencairan,PENUH|required|string|max:150',
            'catatan' => 'required_if:id_status_pencairan,2,3' // Wajib jika ditolak/revisi
        ], $this->aturanPenerima((float) $pengajuan->total_nominal)), array_merge([
            'no_spp.required_if' => 'Nomor SPP wajib diisi untuk metode pembayaran LS.',
            'tgl_spp.required_if' => 'Tanggal SPP wajib diisi untuk metode pembayaran LS.',
            'cara_bayar.required_if' => 'Pilih cara pembayaran (transfer atau tunai).',
            'jenis_pencairan.required_if' => 'Pilih pencairan penuh atau parsial untuk metode UP/TUP.',
            'nama_penerima.required' => 'Nama penerima wajib diisi untuk metode UP/TUP.',
            'tgl_transfer.required_if' => 'Tanggal pembayaran wajib diisi untuk metode UP/TUP.',
            'no_kwitansi.required_if' => 'Nomor kwitansi wajib diisi untuk pembayaran tunai.',
        ], $this->pesanPenerima()));

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
        $pengajuan = Pengajuan::with('status')->findOrFail($id);

        // Konfirmasi hanya sah bila PPSPM sudah menyerahkan kembali ke Bendahara.
        if ($pengajuan->status?->kode_status !== 'PROSES_KONFIRMASI_BENDAHARA') {
            return redirect()
                ->route('bendahara.pengajuan.index')
                ->with('error', 'Pengajuan ini tidak berada pada tahap konfirmasi pencairan.');
        }

        // Berlaku untuk kedua jalur LS: SP2D terbit setelah SPM cair.
        $rules = [
            'no_sp2d' => 'required|string|max:100',
            'tgl_sp2d' => 'required|date',
            'tgl_transfer' => 'required|date',
            'catatan' => 'nullable|string',
        ];

        $pesan = [
            'no_sp2d.required' => 'Nomor SP2D wajib diisi.',
            'tgl_sp2d.required' => 'Tanggal SP2D wajib diisi.',
            'tgl_transfer.required' => 'Tanggal pencairan wajib diisi.',
        ];

        // Berlaku untuk kedua jalur LS: Bendahara meneruskan dana ke penerima,
        // bisa transfer atau tunai, dibayar penuh sekaligus atau bertahap.
        $rules['jenis_pencairan'] = 'required|in:PENUH,PARSIAL';
        // Cara bayar & kwitansi berlaku untuk seluruh penerima pada satu pembayaran.
        $rules['cara_bayar'] = 'required|in:TRANSFER,TUNAI';
        $rules['no_kwitansi'] = 'required_if:cara_bayar,TUNAI|nullable|string|max:100';
        // Nama penerima tunggal hanya dipakai pada pencairan penuh.
        $rules['nama_penerima'] = 'exclude_unless:jenis_pencairan,PENUH|required|string|max:150';
        $rules += $this->aturanPenerima((float) $pengajuan->total_nominal);

        $this->normalkanNominalPenerima($request);

        $pesan += array_merge([
            'cara_bayar.required' => 'Pilih cara pembayaran ke penerima (transfer atau tunai).',
            'jenis_pencairan.required' => 'Pilih pencairan penuh atau parsial.',
            'nama_penerima.required' => 'Nama penerima wajib diisi.',
            'no_kwitansi.required_if' => 'Nomor kwitansi wajib diisi untuk pembayaran tunai.',
        ], $this->pesanPenerima());

        // Pada metode LS, nomor SPM harus sudah diterbitkan PPSPM lebih dulu.
        if (in_array($pengajuan->metode_pembayaran, ['LS_BENDAHARA', 'LS_PIHAK_KETIGA'], true)) {
            $ppspm = $pengajuan->ppspm()->orderByDesc('id_ppspm')->first();

            if (! $ppspm || ! $ppspm->no_spm) {
                return back()->with('error', 'Nomor SPM dari PPSPM belum tersedia. Konfirmasi pencairan belum dapat dilakukan.');
            }
        }

        $request->validate($rules, $pesan);

        $this->pencairanService->bendaharaKonfirmasi($pengajuan, $request->all());

        return redirect()->route('bendahara.pengajuan.index')->with('success', 'Konfirmasi pencairan selesai.');
    }

    /**
     * Pembayaran lanjutan pada pengajuan yang dicairkan bertahap.
     */
    public function bayar(Request $request, $id)
    {
        $pengajuan = Pengajuan::with('status')->findOrFail($id);

        if ($pengajuan->status?->kode_status !== 'PENCAIRAN_PARSIAL') {
            return redirect()
                ->route('bendahara.pengajuan.index')
                ->with('error', 'Pengajuan ini tidak sedang dalam pencairan bertahap.');
        }

        $sisa = $pengajuan->sisa_pembayaran;

        if ($sisa <= 0) {
            return back()->with('error', 'Seluruh nominal pengajuan ini sudah dibayarkan.');
        }

        // Satu pengajuan memakai satu nomor kwitansi. Bila sudah pernah dicatat,
        // termin ini mewarisinya sehingga Bendahara tidak perlu mengisi lagi.
        $kwitansiPengajuan = $pengajuan->no_kwitansi;

        $this->normalkanNominalPenerima($request);

        $request->validate(array_merge([
            'jenis_pencairan' => 'required|in:PENUH,PARSIAL',
            'tgl_transfer' => 'required|date',
            // Cara bayar & kwitansi berlaku untuk seluruh penerima pada satu pembayaran.
            'cara_bayar' => 'required|in:TRANSFER,TUNAI',
            'no_kwitansi' => $kwitansiPengajuan
                ? 'nullable|string|max:100'
                : 'required_if:cara_bayar,TUNAI|nullable|string|max:100',
            // Nama penerima tunggal hanya dipakai pada pencairan penuh.
            'nama_penerima' => 'exclude_unless:jenis_pencairan,PENUH|required|string|max:150',
            'catatan' => 'nullable|string',
            // Pembayaran tidak boleh melebihi sisa yang belum dibayarkan.
        ], $this->aturanPenerima($sisa)), array_merge([
            'cara_bayar.required' => 'Pilih cara pembayaran (transfer atau tunai).',
            'jenis_pencairan.required' => 'Pilih apakah sisa dibayar penuh atau sebagian.',
            'nama_penerima.required' => 'Nama penerima wajib diisi.',
            'tgl_transfer.required' => 'Tanggal pembayaran wajib diisi.',
            'no_kwitansi.required_if' => 'Nomor kwitansi wajib diisi untuk pembayaran tunai.',
        ], $this->pesanPenerima()));

        $this->pencairanService->bendaharaBayarLanjutan($pengajuan, $request->all());

        $pengajuan->refresh();

        return redirect()
            ->route('bendahara.pengajuan.index')
            ->with('success', $pengajuan->isLunas()
                ? 'Pembayaran dicatat. Pengajuan sudah lunas dan berstatus selesai.'
                : 'Pembayaran dicatat. Sisa Rp ' . number_format($pengajuan->sisa_pembayaran, 0, ',', '.') . ' menunggu pembayaran berikutnya.');
    }

    /**
     * Aturan untuk daftar penerima pada pencairan parsial.
     * Satu pembayaran boleh dibagi ke beberapa orang sekaligus, asalkan
     * totalnya tidak melebihi nominal yang masih boleh dibayarkan.
     */
    /**
     * Normalkan nominal tiap penerima sebelum divalidasi.
     *
     * Form mengirim angka murni lewat input hidden, tapi bila nilai berformat
     * "2.500.000" sampai ke sini, aturan 'numeric' akan membacanya sebagai 2,5
     * dan mencatat pembayaran dua setengah rupiah tanpa peringatan.
     * Lihat App\Support\Rupiah::keAngka().
     */
    private function normalkanNominalPenerima(Request $request): void
    {
        $penerima = $request->input('penerima');

        if (! is_array($penerima)) {
            return;
        }

        foreach ($penerima as $i => $baris) {
            if (is_array($baris) && array_key_exists('nominal', $baris)) {
                $penerima[$i]['nominal'] = Rupiah::keAngka($baris['nominal']);
            }
        }

        $request->merge(['penerima' => $penerima]);
    }

    private function aturanPenerima(float $batas): array
    {
        return [
            'penerima' => [
                'required_if:jenis_pencairan,PARSIAL',
                'array',
                'min:1',
                function ($attribute, $value, $fail) use ($batas) {
                    $total = collect($value)->sum(fn ($baris) => (float) ($baris['nominal'] ?? 0));

                    if ($total <= 0) {
                        $fail('Isi nominal untuk minimal satu penerima.');

                        return;
                    }

                    if ($total > $batas) {
                        $fail(sprintf(
                            'Total nominal penerima (Rp %s) melebihi nominal yang boleh dibayarkan (Rp %s).',
                            number_format($total, 0, ',', '.'),
                            number_format($batas, 0, ',', '.')
                        ));
                    }
                },
            ],
            'penerima.*.nama_penerima' => ['required', 'string', 'max:150'],
            'penerima.*.nominal' => ['required', 'numeric', 'min:1'],
        ];
    }

    private function pesanPenerima(): array
    {
        return [
            'penerima.required_if' => 'Tambahkan minimal satu penerima untuk pencairan parsial.',
            'penerima.*.nama_penerima.required' => 'Nama penerima wajib diisi.',
            'penerima.*.nominal.required' => 'Nominal tiap penerima wajib diisi.',
            'penerima.*.nominal.min' => 'Nominal tiap penerima minimal 1.',
        ];
    }

    /**
     * Riwayat dikelompokkan per pengajuan, bukan per catatan proses.
     *
     * Satu pengajuan bisa punya beberapa catatan (SPP, konfirmasi, tiap termin
     * pembayaran), sehingga menampilkan per catatan membuat nomor pengajuan yang
     * sama muncul berulang. Di sini satu baris mewakili satu nomor pengajuan,
     * dengan ringkasan seluruh proses yang pernah Bendahara ini lakukan padanya.
     */
    public function riwayat()
    {
        $idPengajuan = Bendahara::where('id_user', auth()->id())
            ->distinct()
            ->pluck('id_pengajuan');

        $riwayat = Pengajuan::with(['user', 'status', 'bendahara.user'])
            ->whereIn('id_pengajuan', $idPengajuan)
            ->orderByDesc('id_pengajuan')
            ->paginate(15);

        return view('bendahara.riwayat', compact('riwayat'));
    }
}