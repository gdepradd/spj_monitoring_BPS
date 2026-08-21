<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $role = Role::firstOrCreate(
            ['nama_role' => 'pegawai'],
            ['keterangan' => 'Pegawai']
        );

        return [
            'nama_lengkap' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'no_hp' => '08' . fake()->numerify('##########'),
            'id_role' => $role->id_role,
            'status_aktif' => true,
            'urutan_verifikator' => null,
        ];
    }
}