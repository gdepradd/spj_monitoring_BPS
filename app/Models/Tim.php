<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tim extends Model
{
    protected $table = 'tim';
    protected $primaryKey = 'id_tim';

    protected $fillable = [
        'nama_tim',
        'keterangan',
    ];

    /**
     * Anggota tim. Satu orang boleh masuk beberapa tim sekaligus, sehingga
     * keanggotaan disimpan di tabel pivot tim_user, bukan di kolom users.
     */
    public function users(): BelongsToMany
    {
        // Pivot tim_user hanya menyimpan keanggotaan, tanpa kolom waktu.
        return $this->belongsToMany(User::class, 'tim_user', 'id_tim', 'id_user');
    }

    /**
     * Pengajuan yang dibuat atas nama tim ini.
     */
    public function pengajuan(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'id_tim', 'id_tim');
    }
}
