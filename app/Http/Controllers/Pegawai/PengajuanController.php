<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\StorePengajuanRequest;
use App\Http\Requests\Pegawai\UpdatePengajuanRequest;
use App\Models\Pengajuan;
use App\Models\StatusPengajuan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PengajuanController extends Controller
{
    public function index(): View
    {
        $query = Pengajuan::query()
            ->with('status')
            ->where('id_user', Auth::id())
            ->latest('created_at');

        if ($kodeStatus = request('status')) {
            $query->whereHas('status', fn ($q) => $q->where('kode_status', $kodeStatus));
        }

        $pengajuan = $query->paginate(10)->withQueryString();
        $statusList = StatusPengajuan::orderBy('urutan')->get();

        return view('pegawai.pengajuan.index', compact('pengajuan', 'statusList'));
    }

    public function create(): View
    {
        return view('pegawai.pengajuan.create');
    }

    public function store(StorePengajuanRequest $request): RedirectResponse
    {
        $statusDiajukan = StatusPengajuan::where('kode_status', 'DIAJUKAN')->firstOrFail();

        $pengajuan = DB::transaction(function () use ($request, $statusDiajukan) {
            return Pengajuan::create([
                'no_pengajuan' => $this->generateNoPengajuan($request->date('tanggal_pengajuan')->format('Ymd')),
                'id_user' => Auth::id(),
                'tanggal_pengajuan' => $request->date('tanggal_pengajuan'),
                'perihal' => $request->string('perihal')->toString(),
                'keterangan' => $request->string('keterangan')->toString(),
                'total_nominal' => $request->input('total_nominal'),
                'id_status' => $statusDiajukan->id_status,
                'catatan_pengaju' => null,
            ]);
        });

        return redirect()
            ->route('pegawai.pengajuan.show', $pengajuan)
            ->with('success', 'Pengajuan berhasil dibuat dan menunggu proses verifikasi.');
    }

    public function show(Pengajuan $pengajuan): View
    {
        $pengajuan->load(['status', 'pemohon']);
        Gate::authorize('view', $pengajuan);

        $timeline = $this->buildTimeline($pengajuan);

        return view('pegawai.pengajuan.show', compact('pengajuan', 'timeline'));
    }

    public function edit(Pengajuan $pengajuan): View
    {
        $pengajuan->load('status');
        Gate::authorize('update', $pengajuan);

        return view('pegawai.pengajuan.edit', compact('pengajuan'));
    }

    public function update(UpdatePengajuanRequest $request, Pengajuan $pengajuan): RedirectResponse
    {
        $pengajuan->load('status');
        Gate::authorize('update', $pengajuan);

        $statusVerifikasiSatu = StatusPengajuan::where('kode_status', 'VERIFIKASI_1')->firstOrFail();

        $pengajuan->update([
            'tanggal_pengajuan' => $request->date('tanggal_pengajuan'),
            'perihal' => $request->string('perihal')->toString(),
            'keterangan' => $request->string('keterangan')->toString(),
            'total_nominal' => $request->input('total_nominal'),
            'catatan_pengaju' => $request->filled('catatan_pengaju')
                ? $request->string('catatan_pengaju')->toString()
                : null,
            'id_status' => $statusVerifikasiSatu->id_status,
        ]);

        return redirect()
            ->route('pegawai.pengajuan.show', $pengajuan)
            ->with('success', 'Perbaikan berhasil dikirim. Pengajuan kembali ke Verifikator 1.');
    }

    public function riwayat(): View
    {
        $pengajuan = Pengajuan::query()
            ->with('status')
            ->where('id_user', Auth::id())
            ->latest('created_at')
            ->paginate(15);

        return view('pegawai.pengajuan.riwayat', compact('pengajuan'));
    }

    private function generateNoPengajuan(string $tanggal): string
    {
        $prefix = "PGJ-{$tanggal}-";

        $latest = Pengajuan::query()
            ->where('no_pengajuan', 'like', $prefix . '%')
            ->orderByDesc('no_pengajuan')
            ->lockForUpdate()
            ->value('no_pengajuan');

        $sequence = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        if ($sequence > 9999) {
            throw ValidationException::withMessages([
                'tanggal_pengajuan' => 'Nomor pengajuan untuk tanggal tersebut sudah mencapai batas harian.',
            ]);
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function buildTimeline(Pengajuan $pengajuan): Collection
    {
        $items = collect([
            [
                'waktu' => $pengajuan->created_at,
                'judul' => 'Pengajuan dibuat',
                'kode_status' => 'DIAJUKAN',
                'nama_status' => 'Diajukan',
                'catatan' => $pengajuan->catatan_pengaju,
                'aktor' => $pengajuan->pemohon?->name,
            ],
        ]);

        $verifikasi = DB::table('verifikasi as v')
            ->join('status_verifikasi as s', 's.id', '=', 'v.id_status_verifikasi')
            ->join('users as u', 'u.id', '=', 'v.id_verifikator')
            ->where('v.id_pengajuan', $pengajuan->id_pengajuan)
            ->select([
                'v.tanggal_verifikasi as waktu',
                'v.tahap',
                'v.catatan',
                's.kode as kode_status',
                's.nama as nama_status',
                'u.name as aktor',
            ])
            ->get()
            ->map(fn ($item) => [
                'waktu' => $item->waktu,
                'judul' => 'Verifikasi Tahap ' . $item->tahap,
                'kode_status' => $item->kode_status,
                'nama_status' => $item->nama_status,
                'catatan' => $item->catatan,
                'aktor' => $item->aktor,
            ]);

        $items = $items->concat($verifikasi);

        foreach ([
            'ppk' => 'PPK',
            'bendahara' => 'Bendahara',
            'ppspm' => 'PPSPM',
        ] as $table => $label) {
            $idColumn = 'id_' . $table;

            $rows = DB::table("{$table} as p")
                ->join('status_pencairan as s', 's.id', '=', 'p.id_status')
                ->where('p.id_pengajuan', $pengajuan->id_pengajuan)
                ->select([
                    "p.{$idColumn}",
                    'p.tanggal_proses as waktu',
                    'p.catatan',
                    's.kode as kode_status',
                    's.nama as nama_status',
                ])
                ->get()
                ->map(fn ($item) => [
                    'waktu' => $item->waktu,
                    'judul' => 'Proses ' . $label,
                    'kode_status' => $item->kode_status,
                    'nama_status' => $item->nama_status,
                    'catatan' => $item->catatan,
                    'aktor' => $label,
                ]);

            $items = $items->concat($rows);
        }

        return $items
            ->sortBy(fn ($item) => (string) ($item['waktu'] ?? ''))
            ->values();
    }
}
