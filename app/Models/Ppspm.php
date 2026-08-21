<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ppspm extends Model
{
    protected $table = 'ppspm';

    protected $primaryKey = 'id_ppspm';

    public $timestamps = false;

    protected $fillable = [
        'id_pengajuan',
        'id_user',
        'tanggal_proses',
        'id_status',
        'tgl_ajukan_kemenkeu',
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