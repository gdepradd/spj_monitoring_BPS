<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengajuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan';
    protected $primaryKey = 'id_pengajuan';
    
    protected $fillable = [
        'no_pengajuan',
        'id_user',
        'tanggal_pengajuan',
        // Diturunkan otomatis dari keterangan oleh Pegawai\PengajuanController.
        'perihal',
        'keterangan',
        'total_nominal',
        'id_status',
        'metode_pembayaran',
        'catatan_pengaju',
        'bulan_kegiatan',
        'tim_pengaju',
        'nama_penerima',
        // PENUH atau PARSIAL, ditentukan Bendahara saat mulai mencairkan.
        'jenis_pencairan',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'total_nominal' => 'decimal:2',
    ];

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusPengajuan::class, 'id_status', 'id_status');
    }

    public function verifikasi()
    {
        return $this->hasMany(Verifikasi::class, 'id_pengajuan', 'id_pengajuan');    
    }

    public function ppk()
    {
        return $this->hasMany(Ppk::class, 'id_pengajuan', 'id_pengajuan');    
    }

    public function bendahara()
    {
        return $this->hasMany(Bendahara::class, 'id_pengajuan', 'id_pengajuan');   
    }

    public function ppspm()
    {
        return $this->hasMany(Ppspm::class, 'id_pengajuan', 'id_pengajuan');    
    }
    
    /**
     * Catatan pembayaran ke penerima (UP/TUP maupun konfirmasi LS).
     * Satu pengajuan bisa punya banyak baris bila dicairkan bertahap.
     */
    public function pembayaran()
    {
        return $this->hasMany(Bendahara::class, 'id_pengajuan', 'id_pengajuan')
            ->whereIn('tahap', ['PEMBAYARAN_LANGSUNG', 'KONFIRMASI']);
    }

    /**
     * Total nominal yang sudah dibayarkan ke penerima.
     * Memakai relasi yang sudah dimuat bila tersedia agar tidak menambah query.
     */
    public function getTotalDibayarAttribute(): float
    {
        if ($this->relationLoaded('bendahara')) {
            return (float) $this->bendahara
                ->whereIn('tahap', ['PEMBAYARAN_LANGSUNG', 'KONFIRMASI'])
                ->sum('nominal');
        }

        return (float) $this->pembayaran()->sum('nominal');
    }

    public function getSisaPembayaranAttribute(): float
    {
        return max(0, (float) $this->total_nominal - $this->total_dibayar);
    }

    /**
     * Nomor kwitansi yang berlaku untuk pengajuan ini.
     *
     * Satu pengajuan memakai satu nomor kwitansi, jadi nomor yang dicatat pada
     * pembayaran tunai pertama dipakai kembali oleh termin-termin berikutnya.
     */
    public function getNoKwitansiAttribute(): ?string
    {
        if ($this->relationLoaded('bendahara')) {
            return $this->bendahara
                ->whereIn('tahap', ['PEMBAYARAN_LANGSUNG', 'KONFIRMASI'])
                ->whereNotNull('no_kwitansi')
                ->sortBy('id_bendahara')
                ->first()?->no_kwitansi;
        }

        return $this->pembayaran()
            ->whereNotNull('no_kwitansi')
            ->orderBy('id_bendahara')
            ->value('no_kwitansi');
    }

    /**
     * Tanggal pengajuan benar-benar selesai, yaitu saat pembayaran terakhir dicatat.
     * Bernilai null selama pengajuan belum berstatus SELESAI.
     */
    public function getTanggalSelesaiAttribute()
    {
        if ($this->status?->kode_status !== 'SELESAI') {
            return null;
        }

        $terakhir = $this->relationLoaded('bendahara')
            ? $this->bendahara
                ->whereIn('tahap', ['PEMBAYARAN_LANGSUNG', 'KONFIRMASI'])
                ->sortBy('id_bendahara')
                ->last()
            : $this->pembayaran()->orderByDesc('id_bendahara')->first();

        return $terakhir?->tgl_transfer
            ?? $terakhir?->tanggal_proses
            ?? $this->updated_at;
    }

    public function getPersenDibayarAttribute(): float
    {
        $total = (float) $this->total_nominal;

        return $total > 0 ? min(100, round($this->total_dibayar / $total * 100, 1)) : 0;
    }

    public function isLunas(): bool
    {
        return $this->sisa_pembayaran <= 0;
    }

    public function isTahapAktif(string $kodeStatus): bool
    {
        return $this->status?->kode_status === $kodeStatus;
    }

    /**
     * Label metode pembayaran yang dipakai bersama oleh seluruh role.
     */
    public function getMetodeLabelAttribute(): string
    {
        return match ($this->metode_pembayaran) {
            'LS_BENDAHARA' => 'LS Bendahara',
            'LS_PIHAK_KETIGA' => 'LS Pihak Ketiga',
            'UP_TUP' => 'UP / TUP',
            default => 'Belum Ditentukan',
        };
    }

    public function getJenisPencairanLabelAttribute(): string
    {
        return match ($this->jenis_pencairan) {
            'PENUH' => 'Penuh',
            'PARSIAL' => 'Parsial (bertahap)',
            default => '-',
        };
    }

    /**
     * Terjemahkan id_status pada tabel bendahara/ppk/ppspm.
     * Konvensi yang dipakai service: 1 = ACC, 2 = REVISI, 3 = TOLAK.
     */
    protected function labelStatusPencairan($idStatus): string
    {
        return match ((int) $idStatus) {
            1 => 'Selesai',
            2 => 'Revisi',
            3 => 'Ditolak',
            default => 'Selesai',
        };
    }

    protected function namaAktor($model, string $default): string
    {
        return $model?->user->nama_lengkap
            ?? $model?->user->name
            ?? $default;
    }

    /**
     * Satu entri timeline untuk sebuah pembayaran ke penerima.
     * Judul diberi nomor termin bila pencairannya bertahap.
     */
    protected function entriPembayaran($log, string $judul, int $termin, int $jumlahTermin, array $dokumenAwal = []): array
    {
        $tunai = $log->cara_bayar === 'TUNAI';

        $label = $jumlahTermin > 1 ? $judul . ' — Termin ' . $termin : $judul;

        $dokumen = $dokumenAwal + [
            'No. SP2D' => $log->no_sp2d,
            'Tgl. SP2D' => $log->tgl_sp2d,
            'Nominal' => $log->nominal !== null
                ? 'Rp ' . number_format((float) $log->nominal, 0, ',', '.')
                : null,
            'Penerima' => $log->nama_penerima ?? $log->atas_nama_rekening,
            'Cara Bayar' => $tunai ? 'Tunai' : 'Transfer',
            ($tunai ? 'Tgl. Pencairan' : 'Tgl. Transfer') => $log->tgl_transfer,
            'No. Kwitansi' => $log->no_kwitansi,
            // Kolom lama hanya tampil bila datanya memang ada.
            'Nama Bank' => $log->nama_bank,
            'No. Rekening' => $log->no_rekening,
        ];

        return [
            'judul'   => $label,
            'aktor'   => $this->namaAktor($log, 'Bendahara'),
            'waktu'   => $log->tgl_transfer ?? $log->tanggal_proses ?? $log->created_at,
            'catatan' => $log->catatan,
            'status'  => 'Selesai',
            'dokumen' => array_filter($dokumen),
        ];
    }

    /**
     * Entri penutup yang menegaskan apakah pembayaran sudah lunas atau masih bersisa.
     */
    protected function entriSisaPembayaran(): array
    {
        $sisa = $this->sisa_pembayaran;

        return [
            'judul'   => $sisa > 0 ? 'Menunggu Pembayaran Berikutnya' : 'Pembayaran Lunas',
            'aktor'   => 'Bendahara',
            'waktu'   => $sisa > 0 ? null : null,
            'catatan' => null,
            'status'  => $sisa > 0 ? 'Sedang Diproses' : 'Selesai',
            'dokumen' => array_filter([
                'Total Pengajuan' => 'Rp ' . number_format((float) $this->total_nominal, 0, ',', '.'),
                'Sudah Dibayar'   => 'Rp ' . number_format($this->total_dibayar, 0, ',', '.'),
                'Sisa'            => $sisa > 0 ? 'Rp ' . number_format($sisa, 0, ',', '.') : null,
            ]),
        ];
    }

    /**
     * Timeline lengkap pengajuan: pengajuan -> verifikasi -> pencairan.
     * Setiap entri membawa dokumen yang diterbitkan pada tahap tersebut
     * sehingga bisa ditampilkan seragam di semua role.
     */
    public function getRiwayatLengkapAttribute()
    {
        $timeline = collect();

        // 1. Pengajuan Awal
        $timeline->push([
            'judul'   => 'Pengajuan Dibuat',
            'aktor'   => $this->user->nama_lengkap ?? $this->user->name ?? 'Pegawai',
            'waktu'   => $this->created_at,
            'catatan' => $this->catatan_pengaju,
            'status'  => 'Selesai',
            'dokumen' => array_filter([
                'No. Pengajuan'  => $this->no_pengajuan,
                'Tim Pengaju'    => $this->tim_pengaju,
                'Bulan Kegiatan' => $this->bulan_kegiatan,
                'Nama Penerima'  => $this->nama_penerima,
                'Total Nominal'  => 'Rp ' . number_format((float) $this->total_nominal, 0, ',', '.'),
            ]),
        ]);

        // 2. Proses Verifikasi (Tahap 1, 2, 3)
        foreach ($this->verifikasi->sortBy('tahap') as $v) {
            $namaStatus = $v->statusVerifikasi->nama_status ?? null;

            if ($namaStatus) {
                $statusLower = strtolower($namaStatus);
                if (str_contains($statusLower, 'setuju') || str_contains($statusLower, 'acc') || str_contains($statusLower, 'selesai')) {
                    $statusLabel = 'Selesai';
                } elseif (str_contains($statusLower, 'revisi')) {
                    $statusLabel = 'Revisi';
                } elseif (str_contains($statusLower, 'tolak')) {
                    $statusLabel = 'Ditolak';
                } else {
                    $statusLabel = 'Sedang Diproses';
                }
            } else {
                $statusLabel = match ((int) ($v->id_status_verifikasi ?? 0)) {
                    2       => 'Selesai',
                    3       => 'Revisi',
                    4       => 'Ditolak',
                    default => 'Sedang Diproses',
                };
            }

            $timeline->push([
                'judul'   => 'Verifikasi Tahap ' . $v->tahap,
                'aktor'   => $v->verifikator->nama_lengkap ?? $v->verifikator->name ?? ('Verifikator ' . $v->tahap),
                'waktu'   => $v->tanggal_verifikasi ?? $v->created_at,
                'catatan' => $v->catatan,
                'status'  => $statusLabel,
                'dokumen' => array_filter([
                    'Hasil Verifikasi' => $v->statusVerifikasi->nama_status ?? null,
                ]),
            ]);
        }

        // Jika ditolak atau revisi di tahap verifikasi awal
        if (in_array($this->status?->kode_status, ['DITOLAK', 'REVISI']) && $this->bendahara->isEmpty()) {
            return $timeline;
        }

        // 3. Alur Pencairan (Bendahara, PPK, PPSPM)
        $bendaharaLogs = $this->bendahara;

        // Jika belum ada tindakan bendahara sama sekali
        if ($bendaharaLogs->isEmpty() && empty($this->metode_pembayaran)) {
            $timeline->push([
                'judul'   => 'Menunggu Keputusan Bendahara',
                'aktor'   => 'Bendahara',
                'waktu'   => null,
                'catatan' => 'Menunggu Bendahara memilih metode pembayaran.',
                'status'  => 'Sedang Diproses',
                'dokumen' => [],
            ]);
            return $timeline;
        }

        // Cek apakah bendahara menolak/meminta revisi (id_status 2 = REVISI, 3 = TOLAK)
        $logRevisiTolakBendahara = $bendaharaLogs->first(
            fn ($item) => in_array((int) $item->id_status, [2, 3], true)
        );

        if ($logRevisiTolakBendahara) {
            $timeline->push([
                'judul'   => 'Keputusan Bendahara',
                'aktor'   => $this->namaAktor($logRevisiTolakBendahara, 'Bendahara'),
                'waktu'   => $logRevisiTolakBendahara->tanggal_proses ?? $logRevisiTolakBendahara->created_at,
                'catatan' => $logRevisiTolakBendahara->catatan,
                'status'  => $this->labelStatusPencairan($logRevisiTolakBendahara->id_status),
                'dokumen' => [],
            ]);
            return $timeline;
        }

        // Jalur UP / TUP — tiap termin pembayaran menjadi satu entri tersendiri.
        if ($this->metode_pembayaran === 'UP_TUP') {
            $daftarBayar = $bendaharaLogs->where('tahap', 'PEMBAYARAN_LANGSUNG')->sortBy('id_bendahara');

            if ($daftarBayar->isEmpty()) {
                $timeline->push([
                    'judul'   => 'Pembayaran Langsung (UP/TUP)',
                    'aktor'   => 'Bendahara',
                    'waktu'   => null,
                    'catatan' => null,
                    'status'  => 'Sedang Diproses',
                    'dokumen' => array_filter(['Metode' => $this->metode_label]),
                ]);

                return $timeline;
            }

            foreach ($daftarBayar->values() as $urutan => $log) {
                $timeline->push($this->entriPembayaran(
                    $log,
                    'Pembayaran Langsung (UP/TUP)',
                    $urutan + 1,
                    $daftarBayar->count()
                ));
            }

            $timeline->push($this->entriSisaPembayaran());

            return $timeline;
        }

        // Jalur Normal (LS Bendahara / LS Pihak Ketiga)
        $logSpp = $bendaharaLogs->where('tahap', 'PENGAJUAN_SPP')->sortByDesc('id_bendahara')->first();
        $timeline->push([
            'judul'   => 'Pengajuan SPP',
            'aktor'   => $this->namaAktor($logSpp, 'Bendahara'),
            'waktu'   => $logSpp ? ($logSpp->tgl_spp ?? $logSpp->tanggal_proses ?? $logSpp->created_at) : null,
            'catatan' => $logSpp?->catatan,
            'status'  => $logSpp ? 'Selesai' : 'Sedang Diproses',
            'dokumen' => array_filter([
                'Metode'     => $this->metode_label,
                'No. SPP'    => $logSpp?->no_spp,
                'Tgl. SPP'   => $logSpp?->tgl_spp,
            ]),
        ]);

        $logPpk = $this->ppk->sortByDesc('id_ppk')->first();
        $timeline->push([
            'judul'   => 'Persetujuan SPP oleh PPK',
            'aktor'   => $this->namaAktor($logPpk, 'PPK'),
            'waktu'   => $logPpk ? ($logPpk->tgl_spm ?? $logPpk->tanggal_proses ?? $logPpk->created_at) : null,
            'catatan' => $logPpk?->catatan,
            'status'  => $logPpk ? $this->labelStatusPencairan($logPpk->id_status) : ($logSpp ? 'Sedang Diproses' : 'Belum Dimulai'),
            'dokumen' => array_filter([
                'No. SPP Disetujui' => $logSpp?->no_spp,
                'No. SPM'           => $logPpk?->no_spm,
                'Tgl. SPM'          => $logPpk?->tgl_spm,
            ]),
        ]);

        $logPpspm = $this->ppspm->sortByDesc('id_ppspm')->first();
        $timeline->push([
            'judul'   => 'Pengajuan ke Kemenkeu (PPSPM)',
            'aktor'   => $this->namaAktor($logPpspm, 'PPSPM'),
            'waktu'   => $logPpspm ? ($logPpspm->tgl_ajukan_kemenkeu ?? $logPpspm->tanggal_proses ?? $logPpspm->created_at) : null,
            'catatan' => $logPpspm?->catatan,
            'status'  => $logPpspm ? $this->labelStatusPencairan($logPpspm->id_status) : ($logPpk ? 'Sedang Diproses' : 'Belum Dimulai'),
            'dokumen' => array_filter([
                'Tgl. Ajukan Kemenkeu' => $logPpspm?->tgl_ajukan_kemenkeu,
                'No. SPM'              => $logPpspm?->no_spm,
                'Tgl. SPM'             => $logPpspm?->tgl_spm,
            ]),
        ]);

        $daftarKonfirmasi = $bendaharaLogs->where('tahap', 'KONFIRMASI')->sortBy('id_bendahara');

        if ($daftarKonfirmasi->isEmpty()) {
            $timeline->push([
                'judul'   => 'Konfirmasi Pencairan',
                'aktor'   => 'Bendahara',
                'waktu'   => null,
                'catatan' => null,
                'status'  => $logPpspm ? 'Sedang Diproses' : 'Belum Dimulai',
                'dokumen' => array_filter(['No. SPM' => $logPpspm?->no_spm]),
            ]);

            return $timeline;
        }

        foreach ($daftarKonfirmasi->values() as $urutan => $log) {
            $timeline->push($this->entriPembayaran(
                $log,
                'Konfirmasi Pencairan',
                $urutan + 1,
                $daftarKonfirmasi->count(),
                ['No. SPM' => $log->no_spm ?? $logPpspm?->no_spm]
            ));
        }

        $timeline->push($this->entriSisaPembayaran());

        return $timeline;
    }
}