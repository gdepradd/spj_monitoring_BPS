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
        'perihal',
        'keterangan',
        'total_nominal',
        'id_status',
        'metode_pembayaran',
        'catatan_pengaju',
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
            'judul' => 'Pengajuan Dibuat',
            'aktor' => $this->user->nama_lengkap ?? $this->user->name ?? 'Pegawai',
            'waktu' => $this->created_at,
            'catatan' => $this->catatan_pengaju,
            'status' => 'Selesai'
        ]);

        // 2. Proses Verifikasi (Tahap 1, 2, 3)
        foreach ($this->verifikasi as $v) {
            // Memeriksa id_status secara aman untuk menutupi perbedaan penamaan kolom
            $statusId = $v->id_status ?? $v->id_status_verifikasi;
            
            $timeline->push([
                'judul' => 'Verifikasi Tahap ' . $v->tahap,
                'aktor' => $v->user->nama_lengkap ?? $v->user->name ?? 'Verifikator ' . $v->tahap,
                'waktu' => $v->tanggal_verifikasi ?? $v->created_at,
                'catatan' => $v->catatan,
                'status' => $statusId == 1 ? 'Selesai' : ($statusId == 2 ? 'Revisi' : 'Ditolak')
            ]);
        }

        // Jika terhenti di Verifikasi (belum ada data masuk ke Bendahara)
        if (in_array($this->status->kode_status, ['DITOLAK', 'REVISI']) && $this->bendahara->isEmpty()) {
            return $timeline;
        }

        // 3. Memproses Keputusan dan Metode Pencairan
        $bendaharaLogs = $this->bendahara;

        // Jika pengajuan sudah masuk tahap Bendahara tapi belum diproses sama sekali
        if ($bendaharaLogs->isEmpty()) {
            $timeline->push([
                'judul' => 'Menunggu Keputusan Bendahara',
                'aktor' => 'Bendahara',
                'waktu' => null,
                'catatan' => 'Menunggu Bendahara memeriksa pengajuan.',
                'status' => 'Sedang Diproses'
            ]);
            return $timeline;
        }

        // Jika Bendahara menolak atau merevisi pengajuan (id_status 2 atau 3)
        $logPenolakanBendahara = $bendaharaLogs->whereIn('id_status', [2, 3])->first();
        if ($logPenolakanBendahara) {
            $timeline->push([
                'judul' => 'Keputusan Bendahara',
                'aktor' => $logPenolakanBendahara->user->nama_lengkap ?? $logPenolakanBendahara->user->name ?? 'Bendahara',
                'waktu' => $logPenolakanBendahara->tanggal_proses ?? $logPenolakanBendahara->created_at,
                'catatan' => $logPenolakanBendahara->catatan,
                'status' => $logPenolakanBendahara->id_status == 2 ? 'Revisi' : 'Ditolak'
            ]);
            return $timeline;
        }

        // Jika di-ACC, susun linimasa berdasarkan metode pembayaran yang dipilih
        if ($this->metode_pembayaran === 'UP_TUP') {
            $logBayar = $bendaharaLogs->where('tahap', 'PEMBAYARAN_LANGSUNG')->first();
            $timeline->push([
                'judul' => 'Pembayaran Langsung (UP/TUP)',
                'aktor' => 'Bendahara',
                'waktu' => $logBayar ? $logBayar->tanggal_proses : null,
                'catatan' => $logBayar ? $logBayar->catatan : null,
                'status' => $logBayar ? 'Selesai' : 'Sedang Diproses'
            ]);
        } else {
            // Alur Normal (LS Bendahara atau LS Pihak Ketiga)
            $logSpp = $bendaharaLogs->where('tahap', 'PENGAJUAN_SPP')->first();
            $timeline->push([
                'judul' => 'Pengajuan SPP',
                'aktor' => 'Bendahara',
                'waktu' => $logSpp ? $logSpp->tanggal_proses : null,
                'catatan' => $logSpp ? $logSpp->catatan : null,
                'status' => $logSpp ? 'Selesai' : 'Sedang Diproses'
            ]);

            $logPpk = $this->ppk->first();
            $timeline->push([
                'judul' => 'Penerbitan SPM',
                'aktor' => 'PPK',
                'waktu' => $logPpk ? $logPpk->tanggal_proses : null,
                'catatan' => $logPpk ? $logPpk->catatan : null,
                'status' => $logPpk ? 'Selesai' : ($logSpp ? 'Sedang Diproses' : 'Belum Dimulai')
            ]);

            $logPpspm = $this->ppspm->first();
            $timeline->push([
                'judul' => 'Pengajuan ke Kemenkeu',
                'aktor' => 'PPSPM',
                'waktu' => $logPpspm ? $logPpspm->tanggal_proses : null,
                'catatan' => $logPpspm ? $logPpspm->catatan : null,
                'status' => $logPpspm ? 'Selesai' : ($logPpk ? 'Sedang Diproses' : 'Belum Dimulai')
            ]);

            $logKonfirmasi = $bendaharaLogs->where('tahap', 'KONFIRMASI')->first();
            $timeline->push([
                'judul' => 'Konfirmasi Pencairan',
                'aktor' => 'Bendahara',
                'waktu' => $logKonfirmasi ? $logKonfirmasi->tanggal_proses : null,
                'catatan' => $logKonfirmasi ? $logKonfirmasi->catatan : null,
                'status' => $logKonfirmasi ? 'Selesai' : ($logPpspm ? 'Sedang Diproses' : 'Belum Dimulai')
            ]);
        }

        return $timeline;
    }
}