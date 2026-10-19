<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /**
     * Tim yang diikuti akun ini. Satu orang boleh masuk beberapa tim, jadi
     * keanggotaannya ada di tabel pivot tim_user.
     */
    public function tim(): BelongsToMany
    {
        return $this->belongsToMany(Tim::class, 'tim_user', 'id_user', 'id_tim')
            ->orderBy('nama_tim');
    }

    /**
     * Daftar id tim yang diikuti, dipakai untuk menyaring pengajuan rekan satu tim.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function idTim(): \Illuminate\Support\Collection
    {
        return $this->tim->pluck('id_tim');
    }

    /**
     * Nama tim dirangkai jadi satu baris, mis. "Produksi, IPDS".
     * Mengembalikan null bila akun ini belum dimasukkan ke tim mana pun.
     */
    public function namaTim(): ?string
    {
        $nama = $this->tim->pluck('nama_tim');

        return $nama->isEmpty() ? null : $nama->implode(', ');
    }

    public function pengajuan(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'id_user', 'id_user');
    }

    public function isRole(string ...$roles): bool
    {
        return in_array($this->role?->nama_role, $roles, true);
    }

    /**
     * Alias isRole(); dipakai oleh view layouts/navigation.blade.php.
     */
    public function hasRole(string ...$roles): bool
    {
        return $this->isRole(...$roles);
    }
}
