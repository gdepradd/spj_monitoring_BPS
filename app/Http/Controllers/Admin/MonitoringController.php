<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\StatusPencairan;
use App\Models\StatusPengajuan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Modul monitoring untuk role admin (super admin).
 *
 * Seluruh method di sini bersifat baca-saja: admin dapat melihat setiap
 * pengajuan beserta seluruh tahapan dan dokumennya, tetapi tidak memiliki
 * jalur untuk mengubah alur pengajuan.
 */
class MonitoringController extends Controller
{
    /**
     * Tahap yang sedang menunggu tindakan, dipetakan dari kode status pengajuan.
     */
    private const BEBAN_TAHAP = [
        'DIAJUKAN' => 'Menunggu Verifikator 1',
        'VERIFIKASI_1' => 'Di Verifikator 1',
        'VERIFIKASI_2' => 'Di Verifikator 2',
        'VERIFIKASI_3' => 'Di Verifikator 3',
        'MENUNGGU_PENCAIRAN' => 'Di Bendahara (metode & SPP)',
        'PROSES_PPK' => 'Di PPK',
        'PROSES_PPSPM' => 'Di PPSPM',
        'PROSES_KONFIRMASI_BENDAHARA' => 'Di Bendahara (konfirmasi)',
        'REVISI' => 'Dikembalikan ke Pegawai',
    ];

    /*
    |--------------------------------------------------------------------------
    | Dashboard monitoring
    |--------------------------------------------------------------------------
    */
    public function dashboard(): View
    {
        $statusCounts = StatusPengajuan::query()
            ->orderBy('urutan')
            ->withCount('pengajuan as jumlah')
            ->withSum('pengajuan as nominal', 'total_nominal')
            ->get();

        $perStatus = $statusCounts->keyBy('kode_status');

        $total = Pengajuan::count();
        $totalNominal = (float) Pengajuan::sum('total_nominal');

        $selesai = (int) ($perStatus->get('SELESAI')?->jumlah ?? 0);
        $ditolak = (int) ($perStatus->get('DITOLAK')?->jumlah ?? 0);
        $revisi = (int) ($perStatus->get('REVISI')?->jumlah ?? 0);
        $berjalan = $total - $selesai - $ditolak;

        $nominalCair = (float) ($perStatus->get('SELESAI')?->nominal ?? 0);

        // Beban tiap tahap: berapa pengajuan yang saat ini menunggu di sana.
        $beban = collect(self::BEBAN_TAHAP)
            ->map(fn ($label, $kode) => [
                'label' => $label,
                'kode' => $kode,
                'jumlah' => (int) ($perStatus->get($kode)?->jumlah ?? 0),
            ])
            ->values();

        // Pengajuan berjalan paling lama — kandidat yang perlu ditindaklanjuti.
        $terlama = Pengajuan::with(['user', 'status'])
            ->whereHas('status', fn ($q) => $q->whereNotIn('kode_status', ['SELESAI', 'DITOLAK']))
            ->orderBy('tanggal_pengajuan')
            ->limit(8)
            ->get();

        $metode = Pengajuan::query()
            ->selectRaw('metode_pembayaran, COUNT(*) as jumlah, SUM(total_nominal) as nominal')
            ->groupBy('metode_pembayaran')
            ->get();

        // Sepuluh aktivitas terakhir — LIMIT dikerjakan database.
        $aktivitasTerbaru = $this->petakanAktivitas(
            collect(
                $this->queryAktivitas()
                    ->orderByDesc('waktu')
                    ->orderByDesc('id_referensi')
                    ->limit(10)
                    ->get()
            )
        );

        return view('admin.monitoring.dashboard', compact(
            'statusCounts',
            'total',
            'totalNominal',
            'nominalCair',
            'selesai',
            'ditolak',
            'revisi',
            'berjalan',
            'beban',
            'terlama',
            'metode',
            'aktivitasTerbaru'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Daftar seluruh pengajuan lintas role
    |--------------------------------------------------------------------------
    */
    public function pengajuan(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'exists:status_pengajuan,kode_status'],
            'metode' => ['nullable', 'string', 'in:LS_BENDAHARA,LS_PIHAK_KETIGA,UP_TUP,BELUM'],
            'pemohon' => ['nullable', 'integer', 'exists:users,id_user'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $query = Pengajuan::with(['user', 'status'])
            ->withCount(['verifikasi', 'bendahara', 'ppk', 'ppspm']);

        if ($cari = $filter['q'] ?? null) {
            $query->where(function ($q) use ($cari) {
                $q->where('no_pengajuan', 'like', "%{$cari}%")
                    ->orWhere('nama_penerima', 'like', "%{$cari}%")
                    ->orWhere('tim_pengaju', 'like', "%{$cari}%")
                    ->orWhere('keterangan', 'like', "%{$cari}%")
                    ->orWhereHas('user', fn ($u) => $u->where('nama_lengkap', 'like', "%{$cari}%"));
            });
        }

        if ($status = $filter['status'] ?? null) {
            $query->whereHas('status', fn ($q) => $q->where('kode_status', $status));
        }

        if ($metode = $filter['metode'] ?? null) {
            $metode === 'BELUM'
                ? $query->whereNull('metode_pembayaran')
                : $query->where('metode_pembayaran', $metode);
        }

        if ($pemohon = $filter['pemohon'] ?? null) {
            $query->where('id_user', $pemohon);
        }

        if ($dari = $filter['dari'] ?? null) {
            $query->whereDate('tanggal_pengajuan', '>=', $dari);
        }

        if ($sampai = $filter['sampai'] ?? null) {
            $query->whereDate('tanggal_pengajuan', '<=', $sampai);
        }

        // Ringkasan mengikuti filter yang sedang aktif, bukan seluruh tabel.
        // Dihitung sebelum paginate() agar tidak terpengaruh limit/offset.
        $nominalTerfilter = (float) (clone $query)->sum('total_nominal');

        $pengajuan = $query
            ->orderByDesc('id_pengajuan')
            ->paginate(20)
            ->withQueryString();

        $statusList = StatusPengajuan::orderBy('urutan')->get();

        $pemohonList = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pegawai'))
            ->orderBy('nama_lengkap')
            ->get(['id_user', 'nama_lengkap']);

        $ringkasan = [
            'jumlah' => $pengajuan->total(),
            'nominal' => $nominalTerfilter,
        ];

        return view('admin.monitoring.pengajuan', compact(
            'pengajuan',
            'statusList',
            'pemohonList',
            'ringkasan'
        ) + ['filter' => $filter]);
    }

    /*
    |--------------------------------------------------------------------------
    | Detail penuh satu pengajuan
    |--------------------------------------------------------------------------
    */
    public function show($id): View
    {
        $pengajuan = Pengajuan::with([
            'user.role',
            'status',
            'verifikasi' => fn ($q) => $q->orderBy('tahap')->orderBy('id_verifikasi'),
            'verifikasi.verifikator',
            'verifikasi.statusVerifikasi',
            'bendahara' => fn ($q) => $q->orderBy('id_bendahara'),
            'bendahara.user',
            'bendahara.statusPencairan',
            'ppk' => fn ($q) => $q->orderBy('id_ppk'),
            'ppk.user',
            'ppk.statusPencairan',
            'ppspm' => fn ($q) => $q->orderBy('id_ppspm'),
            'ppspm.user',
            'ppspm.statusPencairan',
        ])->findOrFail($id);

        // Feed khusus pengajuan ini, dipakai untuk menghitung lama tiap tahap.
        $aktivitas = $this->petakanAktivitas(
            collect(
                $this->queryAktivitas(['id_pengajuan' => $pengajuan->id_pengajuan])
                    ->orderBy('waktu')
                    ->orderBy('id_referensi')
                    ->get()
            )
        );

        $mulai = $pengajuan->created_at;
        $langkah = $aktivitas->map(function ($item) use (&$mulai) {
            $jeda = $mulai && $item['waktu'] ? $mulai->diffInHours($item['waktu']) : null;
            $mulai = $item['waktu'] ?? $mulai;

            return $item + ['jeda_jam' => $jeda];
        });

        $tahapSekarang = self::BEBAN_TAHAP[$pengajuan->status?->kode_status] ?? null;

        return view('admin.monitoring.show', compact(
            'pengajuan',
            'langkah',
            'tahapSekarang'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | Log aktivitas seluruh role
    |--------------------------------------------------------------------------
    */
    public function aktivitas(Request $request): View
    {
        $filter = $request->validate([
            'peran' => ['nullable', 'string', 'in:Verifikator,Bendahara,PPK,PPSPM'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        // Pengurutan dan paginasi dikerjakan database, bukan dirakit di memori.
        $aktivitas = $this->queryAktivitas($filter)
            ->orderByDesc('waktu')
            ->orderByDesc('id_referensi')
            ->paginate(25)
            ->withQueryString();

        $aktivitas->setCollection(
            $this->petakanAktivitas(collect($aktivitas->items()))
        );

        return view('admin.monitoring.aktivitas', compact('aktivitas') + ['filter' => $filter]);
    }

    /*
    |--------------------------------------------------------------------------
    | Query gabungan log verifikasi + pencairan
    |--------------------------------------------------------------------------
    |
    | Keempat tabel proses disatukan lewat UNION ALL dengan bentuk kolom yang
    | sama, lalu dibungkus subquery agar ORDER BY, COUNT, dan LIMIT berjalan di
    | sisi database. Kolom dokumen yang tidak dimiliki sebuah tabel diisi NULL
    | supaya jumlah dan urutan kolom tiap cabang tetap identik.
    */
    private function queryAktivitas(array $filter = []): QueryBuilder
    {
        $peran = $filter['peran'] ?? null;

        $cabang = collect([
            'Verifikator' => fn () => $this->cabangVerifikasi(),
            'Bendahara' => fn () => $this->cabangPencairan('bendahara', 'id_bendahara', 'Bendahara'),
            'PPK' => fn () => $this->cabangPencairan('ppk', 'id_ppk', 'PPK'),
            'PPSPM' => fn () => $this->cabangPencairan('ppspm', 'id_ppspm', 'PPSPM'),
        ])
            ->when($peran, fn ($daftar) => $daftar->only($peran))
            ->map(fn ($pembuat) => $pembuat())
            ->map(fn ($query) => $this->saringCabang($query, $filter))
            ->values();

        $union = $cabang->shift();

        foreach ($cabang as $lanjutan) {
            $union->unionAll($lanjutan);
        }

        return DB::query()->fromSub($union, 'log');
    }

    /**
     * Cabang UNION untuk tabel verifikasi.
     */
    private function cabangVerifikasi(): QueryBuilder
    {
        return DB::table('verifikasi as v')
            ->leftJoin('users as u', 'u.id_user', '=', 'v.id_verifikator')
            ->leftJoin('status_verifikasi as s', 's.id_status_verifikasi', '=', 'v.id_status_verifikasi')
            ->selectRaw("
                'Verifikator' as sumber,
                v.id_verifikasi as id_referensi,
                v.id_pengajuan as id_pengajuan,
                u.nama_lengkap as aktor,
                COALESCE(v.tanggal_verifikasi, v.created_at) as waktu,
                v.id_status_verifikasi as status_id,
                s.nama_status as status_nama,
                v.catatan as catatan,
                CAST(v.tahap AS CHAR) as tahap,
                NULL as no_spp,
                NULL as tgl_spp,
                NULL as no_spm,
                NULL as tgl_spm,
                NULL as no_sp2d,
                NULL as tgl_sp2d,
                NULL as tgl_transfer,
                NULL as tgl_ajukan_kemenkeu,
                NULL as nama_bank,
                NULL as no_rekening,
                NULL as atas_nama_rekening
            ");
    }

    /**
     * Cabang UNION untuk tabel pencairan (bendahara, ppk, ppspm).
     * Kolom yang tidak ada pada tabel terkait diisi NULL.
     */
    private function cabangPencairan(string $tabel, string $primaryKey, string $sumber): QueryBuilder
    {
        $tersedia = $this->kolomTabel($tabel);

        $kolom = fn (string $nama) => in_array($nama, $tersedia, true)
            ? "t.{$nama} as {$nama}"
            : "NULL as {$nama}";

        return DB::table("{$tabel} as t")
            ->leftJoin('users as u', 'u.id_user', '=', 't.id_user')
            ->leftJoin('status_pencairan as s', 's.id_status_pencairan', '=', 't.id_status')
            ->selectRaw("
                '{$sumber}' as sumber,
                t.{$primaryKey} as id_referensi,
                t.id_pengajuan as id_pengajuan,
                u.nama_lengkap as aktor,
                COALESCE(t.tanggal_proses, t.created_at) as waktu,
                t.id_status as status_id,
                s.nama_status as status_nama,
                t.catatan as catatan,
                " . $kolom('tahap') . ",
                " . $kolom('no_spp') . ",
                " . $kolom('tgl_spp') . ",
                " . $kolom('no_spm') . ",
                " . $kolom('tgl_spm') . ",
                " . $kolom('no_sp2d') . ",
                " . $kolom('tgl_sp2d') . ",
                " . $kolom('tgl_transfer') . ",
                " . $kolom('tgl_ajukan_kemenkeu') . ",
                " . $kolom('nama_bank') . ",
                " . $kolom('no_rekening') . ",
                " . $kolom('atas_nama_rekening') . "
            ");
    }

    /**
     * Daftar kolom tabel, di-cache per proses.
     *
     * Tanpa cache, penyusunan cabang UNION memanggil Schema::hasColumn puluhan
     * kali dan tiap panggilan menembak information_schema.
     */
    private function kolomTabel(string $tabel): array
    {
        static $cache = [];

        return $cache[$tabel] ??= Schema::getColumnListing($tabel);
    }

    /**
     * Filter diterapkan di tiap cabang sebelum UNION agar baris yang tidak
     * relevan tidak ikut disatukan.
     */
    private function saringCabang(QueryBuilder $query, array $filter): QueryBuilder
    {
        $tabel = str_contains((string) $query->from, 'verifikasi') ? 'v' : 't';
        $kolomWaktu = $tabel === 'v'
            ? 'COALESCE(v.tanggal_verifikasi, v.created_at)'
            : 'COALESCE(t.tanggal_proses, t.created_at)';

        if ($id = $filter['id_pengajuan'] ?? null) {
            $query->where("{$tabel}.id_pengajuan", $id);
        }

        if ($dari = $filter['dari'] ?? null) {
            $query->whereRaw("DATE({$kolomWaktu}) >= ?", [$dari]);
        }

        if ($sampai = $filter['sampai'] ?? null) {
            $query->whereRaw("DATE({$kolomWaktu}) <= ?", [$sampai]);
        }

        return $query;
    }

    /**
     * Ubah baris mentah hasil UNION menjadi bentuk yang dipakai view.
     * Data pengajuan diambil sekali untuk seluruh baris (hindari N+1).
     */
    private function petakanAktivitas(Collection $baris): Collection
    {
        $pengajuan = Pengajuan::with('user')
            ->whereIn('id_pengajuan', $baris->pluck('id_pengajuan')->unique()->filter())
            ->get()
            ->keyBy('id_pengajuan');

        return $baris->map(function ($row) use ($pengajuan) {
            $aksi = match ($row->sumber) {
                'Verifikator' => 'Verifikasi Tahap ' . $row->tahap,
                'Bendahara' => match ($row->tahap) {
                    'PENGAJUAN_SPP' => 'Pengajuan SPP',
                    'PEMBAYARAN_LANGSUNG' => 'Pembayaran Langsung (UP/TUP)',
                    'KONFIRMASI' => 'Konfirmasi Pencairan',
                    default => 'Keputusan Bendahara',
                },
                'PPK' => 'Persetujuan SPP',
                default => 'Pengajuan ke Kemenkeu',
            };

            $status = $row->status_nama ?: ($row->sumber === 'Verifikator'
                ? $this->labelStatusVerifikasi($row->status_id)
                : $this->labelStatusPencairan($row->status_id));

            $dokumen = match ($row->sumber) {
                'Bendahara' => [
                    'No. SPP' => $row->no_spp,
                    'Tgl. SPP' => $this->tanggal($row->tgl_spp),
                    'No. SPM' => $row->no_spm,
                    'No. SP2D' => $row->no_sp2d,
                    'Tgl. SP2D' => $this->tanggal($row->tgl_sp2d),
                    'Tgl. Transfer' => $this->tanggal($row->tgl_transfer),
                    'Bank' => $row->nama_bank,
                    'No. Rekening' => $row->no_rekening,
                    'Atas Nama' => $row->atas_nama_rekening,
                ],
                'PPK' => [
                    'No. SPM' => $row->no_spm,
                    'Tgl. SPM' => $this->tanggal($row->tgl_spm),
                ],
                'PPSPM' => [
                    'Tgl. Kemenkeu' => $this->tanggal($row->tgl_ajukan_kemenkeu),
                    'No. SPM' => $row->no_spm,
                    'Tgl. SPM' => $this->tanggal($row->tgl_spm),
                    'No. SP2D' => $row->no_sp2d,
                    'Tgl. SP2D' => $this->tanggal($row->tgl_sp2d),
                ],
                default => [],
            };

            return [
                'peran' => $row->sumber,
                'aksi' => $aksi,
                'status' => $status,
                'aktor' => $row->aktor ?: '-',
                'waktu' => $row->waktu ? \Carbon\Carbon::parse($row->waktu) : null,
                'catatan' => $row->catatan,
                'pengajuan' => $pengajuan->get($row->id_pengajuan),
                'dokumen' => array_filter($dokumen),
            ];
        });
    }

    private function labelStatusVerifikasi($idStatus): string
    {
        return match ((int) $idStatus) {
            2 => 'ACC',
            3 => 'Revisi',
            4 => 'Ditolak',
            default => 'Menunggu',
        };
    }

    private function labelStatusPencairan($idStatus): string
    {
        return match ((int) $idStatus) {
            StatusPencairan::ACC => 'ACC',
            StatusPencairan::REVISI => 'Revisi',
            StatusPencairan::DITOLAK => 'Ditolak',
            default => 'Selesai',
        };
    }

    private function tanggal($nilai): ?string
    {
        return $nilai ? \Carbon\Carbon::parse($nilai)->format('d/m/Y') : null;
    }
}
