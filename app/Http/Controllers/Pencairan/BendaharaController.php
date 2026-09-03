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
            'bendahara.user',
            'ppk.user',
            'ppspm.user',
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
        $pengajuan = Pengajuan::with('status')->findOrFail($id);

        // Cegah pengajuan diproses ulang dari tahap yang sudah lewat.
        if ($pengajuan->status?->kode_status !== 'MENUNGGU_PENCAIRAN') {
            return redirect()
                ->route('bendahara.pengajuan.index')
                ->with('error', 'Pengajuan ini tidak berada pada tahap pemilihan metode pembayaran.');
        }

        $request->validate([
            'id_status_pencairan' => 'required|in:1,2,3', // 1=ACC, 2=REVISI, 3=TOLAK
            'metode_pembayaran' => 'required_if:id_status_pencairan,1|nullable|in:LS_BENDAHARA,LS_PIHAK_KETIGA,UP_TUP',
            // Dokumen SPP wajib pada metode LS agar bisa dibaca PPK, PPSPM, dan saat konfirmasi.
            'no_spp' => 'required_if:metode_pembayaran,LS_BENDAHARA,LS_PIHAK_KETIGA',
            'tgl_spp' => 'required_if:metode_pembayaran,LS_BENDAHARA,LS_PIHAK_KETIGA|nullable|date',
            // UP/TUP selesai di tangan Bendahara: bisa lewat transfer atau tunai.
            'cara_bayar' => 'required_if:metode_pembayaran,UP_TUP|nullable|in:TRANSFER,TUNAI',
            'tgl_transfer' => 'required_if:metode_pembayaran,UP_TUP|nullable|date',
            // Jalur transfer: data rekening tujuan.
            'nama_bank' => 'required_if:cara_bayar,TRANSFER|nullable|string|max:100',
            'no_rekening' => 'required_if:cara_bayar,TRANSFER|nullable|string|max:50',
            'atas_nama_rekening' => 'required_if:cara_bayar,TRANSFER|nullable|string|max:150',
            // Jalur tunai: nomor kwitansi sebagai bukti pembayaran.
            'no_kwitansi' => 'required_if:cara_bayar,TUNAI|nullable|string|max:100',
            'catatan' => 'required_if:id_status_pencairan,2,3' // Wajib jika ditolak/revisi
        ], [
            'no_spp.required_if' => 'Nomor SPP wajib diisi untuk metode pembayaran LS.',
            'tgl_spp.required_if' => 'Tanggal SPP wajib diisi untuk metode pembayaran LS.',
            'cara_bayar.required_if' => 'Pilih cara pembayaran (transfer atau tunai) untuk metode UP/TUP.',
            'tgl_transfer.required_if' => 'Tanggal pembayaran wajib diisi untuk metode UP/TUP.',
            'nama_bank.required_if' => 'Nama bank wajib diisi untuk pembayaran transfer.',
            'no_rekening.required_if' => 'Nomor rekening wajib diisi untuk pembayaran transfer.',
            'atas_nama_rekening.required_if' => 'Atas nama rekening wajib diisi untuk pembayaran transfer.',
            'no_kwitansi.required_if' => 'Nomor kwitansi wajib diisi untuk pembayaran tunai.',
        ]);

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

        if ($pengajuan->metode_pembayaran === 'LS_PIHAK_KETIGA') {
            // Kemenkeu mentransfer langsung ke pihak ketiga: Bendahara hanya
            // mencatat SP2D dan tanggal cair, tanpa cara bayar dan rekening.
            $pesan['tgl_transfer.required'] = 'Tanggal cair dari Kemenkeu wajib diisi.';
        } else {
            // LS Bendahara: dana diteruskan Bendahara, bisa transfer atau tunai.
            $rules['cara_bayar'] = 'required|in:TRANSFER,TUNAI';
            $rules['nama_bank'] = 'required_if:cara_bayar,TRANSFER|nullable|string|max:100';
            $rules['no_rekening'] = 'required_if:cara_bayar,TRANSFER|nullable|string|max:50';
            $rules['atas_nama_rekening'] = 'required_if:cara_bayar,TRANSFER|nullable|string|max:150';
            $rules['no_kwitansi'] = 'required_if:cara_bayar,TUNAI|nullable|string|max:100';

            $pesan += [
                'cara_bayar.required' => 'Pilih cara pembayaran ke penerima (transfer atau tunai).',
                'nama_bank.required_if' => 'Nama bank wajib diisi untuk pembayaran transfer.',
                'no_rekening.required_if' => 'Nomor rekening wajib diisi untuk pembayaran transfer.',
                'atas_nama_rekening.required_if' => 'Atas nama rekening wajib diisi untuk pembayaran transfer.',
                'no_kwitansi.required_if' => 'Nomor kwitansi wajib diisi untuk pembayaran tunai.',
            ];
        }

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

    public function riwayat()
    {
        $riwayat = Bendahara::where('id_user', auth()->id())
                    ->with(['pengajuan.user', 'pengajuan.status'])
                    ->orderByDesc('id_bendahara')
                    ->paginate(15);
                    
        return view('bendahara.riwayat', compact('riwayat'));
    }
}