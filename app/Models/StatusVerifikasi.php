<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusVerifikasi extends Model
{
    protected $table = 'status_verifikasi';
    protected $primaryKey = 'id_status_verifikasi';
    public $timestamps = false;

    protected $fillable = [
        'kode_status',
        'nama_status',
        'keterangan',
    ];
}
