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

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusPengajuan::class, 'id_status', 'id_status');
    }
}
