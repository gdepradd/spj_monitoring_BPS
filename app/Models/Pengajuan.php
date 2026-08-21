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
        return $this->hasMany(
                Verifikasi::class,
                'id_pengajuan',
                'id_pengajuan'
            );    
    }
    public function ppk()
    {
        return $this->hasMany(
                Ppk::class,
                'id_pengajuan',
                'id_pengajuan'
        );    
    }

    public function bendahara()
    {
        return $this->hasMany(
                Bendahara::class,
                'id_pengajuan',
                'id_pengajuan'
            );   
    }

    public function ppspm()
    {
        return $this->hasMany(
                Ppspm::class,
                'id_pengajuan',
                'id_pengajuan'
            );    
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
            'aktor' => $this->user->nama_lengkap ?? $this->user->name,
            'waktu' => $this->created_at,
            'catatan' => $this->catatan_pengaju,
            'status' => 'Selesai'
        ]);

        // 2. Proses Verifikasi (Tahap 1, 2, 3)
        foreach ($this->verifikasi as $v) {
            $timeline->push([
                'judul' => 'Verifikasi Tahap ' . $v->tahap,
                'aktor' => $v->verifikator->name ?? 'Verifikator ' . $v->tahap,
                'waktu' => $v->tanggal_verifikasi,
                'catatan' => $v->catatan,
                'status' => $v->id_status_verifikasi == 1 ? 'Selesai' : ($v->id_status_verifikasi == 2 ? 'Revisi' : 'Ditolak')
            ]);
        }

        // Jika ditolak/revisi di verifikasi, hentikan pembacaan linimasa
        if (in_array($this->status->kode_status, ['DITOLAK', 'REVISI'])) {
            return $timeline;
        }

        // 3. Cabang Berdasarkan Metode Pembayaran
        if (empty($this->metode_pembayaran)) {
            $timeline->push([
                'judul' => 'Menunggu Keputusan Bendahara',
                'aktor' => 'Bendahara',
                'waktu' => null,
                'catatan' => 'Menunggu Bendahara memilih metode pembayaran.',
                'status' => 'Sedang Diproses'
            ]);
        } else {
            // Ambil data pencairan dari relasi
            $bendaharaLogs = \App\Models\Bendahara::where('id_pengajuan', $this->id_pengajuan)->get();
            
            if ($this->metode_pembayaran === 'UP_TUP') {
                // Alur UP_TUP: Hanya Bayar Langsung
                $logBayar = $bendaharaLogs->where('tahap', 'PEMBAYARAN_LANGSUNG')->first();
                $timeline->push([
                    'judul' => 'Pembayaran Langsung (UP/TUP)',
                    'aktor' => 'Bendahara',
                    'waktu' => $logBayar ? $logBayar->tanggal_proses : null,
                    'catatan' => $logBayar ? $logBayar->catatan : null,
                    'status' => $logBayar ? 'Selesai' : 'Belum Dimulai'
                ]);
            } else {
                // Alur LS (LS Bendahara / LS Pihak Ketiga)
                // A. SPP Bendahara
                $logSpp = $bendaharaLogs->where('tahap', 'PENGAJUAN_SPP')->first();
                $timeline->push([
                    'judul' => 'Pengajuan SPP',
                    'aktor' => 'Bendahara',
                    'waktu' => $logSpp ? $logSpp->tanggal_proses : null,
                    'catatan' => $logSpp ? $logSpp->catatan : null,
                    'status' => $logSpp ? 'Selesai' : 'Belum Dimulai'
                ]);

                // B. SPM PPK
                $logPpk = \App\Models\Ppk::where('id_pengajuan', $this->id_pengajuan)->first();
                $timeline->push([
                    'judul' => 'Penerbitan SPM',
                    'aktor' => 'PPK',
                    'waktu' => $logPpk ? $logPpk->tanggal_proses : null,
                    'catatan' => $logPpk ? $logPpk->catatan : null,
                    'status' => $logPpk ? 'Selesai' : ($logSpp ? 'Sedang Diproses' : 'Belum Dimulai')
                ]);

                // C. Ajukan Kemenkeu PPSPM
                $logPpspm = \App\Models\Ppspm::where('id_pengajuan', $this->id_pengajuan)->first();
                $timeline->push([
                    'judul' => 'Pengajuan ke Kemenkeu',
                    'aktor' => 'PPSPM',
                    'waktu' => $logPpspm ? $logPpspm->tanggal_proses : null,
                    'catatan' => $logPpspm ? $logPpspm->catatan : null,
                    'status' => $logPpspm ? 'Selesai' : ($logPpk ? 'Sedang Diproses' : 'Belum Dimulai')
                ]);

                // D. Konfirmasi Bendahara
                $logKonfirmasi = $bendaharaLogs->where('tahap', 'KONFIRMASI')->first();
                $timeline->push([
                    'judul' => 'Konfirmasi Pencairan',
                    'aktor' => 'Bendahara',
                    'waktu' => $logKonfirmasi ? $logKonfirmasi->tanggal_proses : null,
                    'catatan' => $logKonfirmasi ? $logKonfirmasi->catatan : null,
                    'status' => $logKonfirmasi ? 'Selesai' : ($logPpspm ? 'Sedang Diproses' : 'Belum Dimulai')
                ]);
            }
        }

        return $timeline;
    }
    
    
}
