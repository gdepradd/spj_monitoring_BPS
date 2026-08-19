<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verifikasi extends Model
{
    use HasFactory;

    protected $table = 'verifikasi';
    protected $primaryKey = 'id_verifikasi';

    protected $fillable = [
        'id_pengajuan',
        'id_verifikator',
        'tahap',
        'tanggal_verifikasi',
        'id_status_verifikasi',
        'catatan',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'id_verifikator', 'id');
    }

    public function statusVerifikasi()
    {
        return $this->belongsTo(StatusVerifikasi::class, 'id_status_verifikasi', 'id');
    }
}