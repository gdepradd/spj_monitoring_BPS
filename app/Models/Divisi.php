<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Divisi extends Model
{
    protected $table = 'divisi';
    protected $primaryKey = 'id_divisi';

    protected $fillable = [
        'nama_divisi',
        'keterangan',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_divisi', 'id_divisi');
    }
}
