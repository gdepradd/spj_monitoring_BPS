<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bendahara extends Model
{
    use HasFactory;

    protected $table = 'bendahara';

    protected $primaryKey = 'id_bendahara';

    /*
    |--------------------------------------------------------------------------
    | Nonaktifkan timestamps otomatis Laravel
    |--------------------------------------------------------------------------
    |
    | Tabel bendahara hanya memiliki created_at,
    | tidak memiliki updated_at.
    |
    */
    public $timestamps = false;

    protected $fillable = [
        'id_pengajuan',
        'tanggal_proses',
        'id_status',
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

    public function statusPencairan()
    {
        return $this->belongsTo(
            StatusPencairan::class,
            'id_status',
            'id_status_pencairan'
        );
    }
}