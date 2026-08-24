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
        // 'perihal',
        'keterangan',
        'total_nominal',
        'id_status',
        'metode_pembayaran',
        'catatan_pengaju',
        'bulan_kegiatan',
        'tim_pengaju',
        'nama_penerima',
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
    
    public function isTahapAktif(string $kodeStatus): bool
    {
        return $this->status?->kode_status === $kodeStatus;
    }

   public function getRiwayatLengkapAttribute()
    {
        $timeline = collect();

        // 1. Pengajuan Awal
        $timeline->push([
            'judul'   => 'Pengajuan Dibuat',
            'aktor'   => $this->user->nama_lengkap ?? $this->user->name ?? 'Pegawai',
            'waktu'   => $this->created_at,
            'catatan' => $this->catatan_pengaju,
            'status'  => 'Selesai'
        ]);

        // 2. Proses Verifikasi (Tahap 1, 2, 3)
        foreach ($this->verifikasi as $v) {
            // Evaluasi status verifikasi berdasarkan relasi atau ID secara dinamis
            $namaStatus = $v->statusVerifikasi->nama_status 
                ?? $v->status->nama_status 
                ?? null;

            if ($namaStatus) {
                $statusLower = strtolower($namaStatus);
                if (str_contains($statusLower, 'setuju') || str_contains($statusLower, 'acc') || str_contains($statusLower, 'selesai')) {
                    $statusLabel = 'Selesai';
                } elseif (str_contains($statusLower, 'revisi')) {
                    $statusLabel = 'Revisi';
                } else {
                    $statusLabel = 'Ditolak';
                }
            } else {
                // Fallback jika relasi belum termuat
                $statusId = $v->id_status_verifikasi ?? $v->id_status ?? 1;
                $statusLabel = match ((int) $statusId) {
                    1       => 'Selesai',
                    2       => 'Revisi',
                    default => 'Ditolak',
                };
            }

            $timeline->push([
                'judul'   => 'Verifikasi Tahap ' . $v->tahap,
                'aktor'   => $v->verifikator->nama_lengkap ?? $v->verifikator->name ?? $v->user->nama_lengkap ?? $v->user->name ?? ('Verifikator ' . $v->tahap),
                'waktu'   => $v->tanggal_verifikasi ?? $v->created_at,
                'catatan' => $v->catatan,
                'status'  => $statusLabel
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
                'status'  => 'Sedang Diproses'
            ]);
            return $timeline;
        }

        // Cek apakah bendahara menolak/meminta revisi
        $logRevisiTolakBendahara = $bendaharaLogs->first(function ($item) {
            $kode = strtolower($item->statusPencairan->nama_status ?? $item->status->nama_status ?? '');
            return str_contains($kode, 'revisi') || str_contains($kode, 'tolak') || in_array($item->id_status, [2, 3]);
        });

        if ($logRevisiTolakBendahara) {
            $statusStr = strtolower($logRevisiTolakBendahara->statusPencairan->nama_status ?? '');
            $timeline->push([
                'judul'   => 'Keputusan Bendahara',
                'aktor'   => $logRevisiTolakBendahara->user->nama_lengkap ?? $logRevisiTolakBendahara->user->name ?? 'Bendahara',
                'waktu'   => $logRevisiTolakBendahara->tanggal_proses ?? $logRevisiTolakBendahara->created_at,
                'catatan' => $logRevisiTolakBendahara->catatan,
                'status'  => str_contains($statusStr, 'revisi') ? 'Revisi' : 'Ditolak'
            ]);
            return $timeline;
        }

        // Jalur UP / TUP
        if ($this->metode_pembayaran === 'UP_TUP') {
            $logBayar = $bendaharaLogs->where('tahap', 'PEMBAYARAN_LANGSUNG')->first();
            $timeline->push([
                'judul'   => 'Pembayaran Langsung (UP/TUP)',
                'aktor'   => 'Bendahara',
                'waktu'   => $logBayar ? ($logBayar->tgl_transfer ?? $logBayar->tanggal_proses ?? $logBayar->created_at) : null,
                'catatan' => $logBayar?->catatan,
                'status'  => $logBayar ? 'Selesai' : 'Sedang Diproses'
            ]);
            return $timeline;
        }

        // Jalur Normal (LS Bendahara / LS Pihak Ketiga)
        $logSpp = $bendaharaLogs->where('tahap', 'PENGAJUAN_SPP')->first();
        $timeline->push([
            'judul'   => 'Pengajuan SPP',
            'aktor'   => 'Bendahara',
            'waktu'   => $logSpp ? ($logSpp->tgl_spp ?? $logSpp->tanggal_proses ?? $logSpp->created_at) : null,
            'catatan' => $logSpp?->catatan,
            'status'  => $logSpp ? 'Selesai' : 'Sedang Diproses'
        ]);

        $logPpk = $this->ppk->first();
        $timeline->push([
            'judul'   => 'Penerbitan SPM',
            'aktor'   => 'PPK',
            'waktu'   => $logPpk ? ($logPpk->tgl_spm ?? $logPpk->tanggal_proses ?? $logPpk->created_at) : null,
            'catatan' => $logPpk?->catatan,
            'status'  => $logPpk ? 'Selesai' : ($logSpp ? 'Sedang Diproses' : 'Belum Dimulai')
        ]);

        $logPpspm = $this->ppspm->first();
        $timeline->push([
            'judul'   => 'Pengajuan ke Kemenkeu',
            'aktor'   => 'PPSPM',
            'waktu'   => $logPpspm ? ($logPpspm->tgl_ajukan_kemenkeu ?? $logPpspm->tanggal_proses ?? $logPpspm->created_at) : null,
            'catatan' => $logPpspm?->catatan,
            'status'  => $logPpspm ? 'Selesai' : ($logPpk ? 'Sedang Diproses' : 'Belum Dimulai')
        ]);

        $logKonfirmasi = $bendaharaLogs->where('tahap', 'KONFIRMASI')->first();
        $timeline->push([
            'judul'   => 'Konfirmasi Pencairan',
            'aktor'   => 'Bendahara',
            'waktu'   => $logKonfirmasi ? ($logKonfirmasi->tgl_transfer ?? $logKonfirmasi->tanggal_proses ?? $logKonfirmasi->created_at) : null,
            'catatan' => $logKonfirmasi?->catatan,
            'status'  => $logKonfirmasi ? 'Selesai' : ($logPpspm ? 'Sedang Diproses' : 'Belum Dimulai')
        ]);

        return $timeline;
    }
}