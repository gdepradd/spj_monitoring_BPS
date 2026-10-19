<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris usulan penerima pembayaran, diisi pengaju saat membuat SPJ.
 *
 * Ini usulan, bukan bukti pembayaran. Catatan uang yang benar-benar keluar
 * tetap berada di tabel bendahara.
 */
class PenerimaPengajuan extends Model
{
    protected $table = 'penerima_pengajuan';
    protected $primaryKey = 'id_penerima_pengajuan';

    protected $fillable = [
        'id_pengajuan',
        'urutan',
        'nama_penerima',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class, 'id_pengajuan', 'id_pengajuan');
    }
}
