<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusPencairan extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Konvensi id_status pada tabel bendahara, ppk, dan ppspm
    |--------------------------------------------------------------------------
    | Nilai ini dipakai form (radio "Keputusan") sampai ke database, sehingga
    | urutan barisnya tidak boleh diubah tanpa memindahkan data terkait.
    */
    public const ACC = 1;
    public const REVISI = 2;
    public const DITOLAK = 3;
    public const SELESAI = 4;

    protected $table = 'status_pencairan';
    protected $primaryKey = 'id_status_pencairan';
    public $timestamps = false;

    protected $fillable = [
        'kode_status',
        'nama_status',
        'keterangan',
    ];
}
