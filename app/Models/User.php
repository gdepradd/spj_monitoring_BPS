<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    /**
     * Requirement tabel users tidak memiliki kolom remember_token.
     * Karena itu fitur "remember me" Breeze harus tidak digunakan.
     */
    protected $rememberTokenName = null;

    protected $fillable = [
        'nama_lengkap',
        'email',
        'password',
        'no_hp',
        'id_role',
        'status_aktif',
        'urutan_verifikator',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'password' => 'hashed',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function pengajuan(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'id_user', 'id_user');
    }

    public function isRole(string ...$roles): bool
    {
        return in_array($this->role?->nama_role, $roles, true);
    }
}
