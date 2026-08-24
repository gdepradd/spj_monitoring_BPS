<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ppk extends Model
{
    protected $table = 'ppk';

    protected $primaryKey = 'id_ppk';

    public $timestamps = false;

    protected $fillable = [
        'id_pengajuan',
        'id_user',
        'tanggal_proses',
        'id_status',
        'no_spm',
        'tgl_spm',
        'catatan',
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