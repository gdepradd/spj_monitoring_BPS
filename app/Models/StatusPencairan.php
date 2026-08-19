<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusPencairan extends Model
{
    protected $table = 'status_pencairan';
    protected $primaryKey = 'id_status_pencairan';
    public $timestamps = false;

    protected $fillable = [
        'kode_status',
        'nama_status',
        'keterangan',
    ];
}
