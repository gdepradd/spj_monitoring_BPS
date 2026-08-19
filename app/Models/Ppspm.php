<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ppspm extends Model
{
    use HasFactory;

    protected $table = 'ppspm';
    protected $primaryKey = 'id_ppspm';

    protected $fillable = [
        'id_pengajuan',
        'tanggal_proses',
        'id_status',
        'catatan',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function statusPencairan()
    {
        return $this->belongsTo(StatusPencairan::class, 'id_status', 'id');
    }
}