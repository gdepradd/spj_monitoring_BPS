<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusPengajuan extends Model
{
    protected $table = 'status_pengajuan';
    protected $primaryKey = 'id_status';
    public $timestamps = false;

    protected $fillable = [
        'kode_status',
        'nama_status',
        'keterangan',
        'urutan',
    ];

    public function pengajuan(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'id_status', 'id_status');
    }
}
