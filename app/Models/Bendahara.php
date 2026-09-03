<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bendahara extends Model
{
    protected $table = 'bendahara';

    protected $primaryKey = 'id_bendahara';

    public $timestamps = false;

    protected $fillable = [
        'id_pengajuan',
        'id_user',
        'tahap',
        // UP/TUP bisa transfer atau tunai; kwitansi dipakai pada pembayaran tunai.
        'cara_bayar',
        'no_kwitansi',
        'tanggal_proses',
        'no_spp',
        'tgl_spp',
        'no_spm',
        'tgl_transfer',
        'no_sp2d',
        'tgl_sp2d',
        'id_status',
        'catatan',
        'no_rekening',
        'nama_bank',
        'atas_nama_rekening',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(
            Pengajuan::class,
            'id_pengajuan',
            'id_pengajuan'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'id_user',
            'id_user'
        );
    }

    public function statusPencairan()
    {
        return $this->belongsTo(
            StatusPencairan::class,
            'id_status',
            'id_status_pencairan'
        );
    }
}