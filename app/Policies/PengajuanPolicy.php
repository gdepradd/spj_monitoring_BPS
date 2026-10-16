<?php

namespace App\Policies;

use App\Models\Pengajuan;
use App\Models\User;

class PengajuanPolicy
{
    /**
     * Peran yang boleh membuat dan mengelola pengajuan miliknya sendiri.
     * Semua peran pelaksana ikut serta: verifikator/PPK/bendahara/PPSPM juga
     * pegawai yang sewaktu-waktu perlu mengajukan SPJ. Admin tidak termasuk
     * karena perannya hanya memantau alur, tidak mengisinya.
     */
    public const PERAN_PENGAJU = ['pegawai', 'verifikator', 'ppk', 'bendahara', 'ppspm'];

    /**
     * Membuat pengajuan baru tidak terikat satu record, jadi cukup perannya
     * yang diperiksa. Dipakai oleh StorePengajuanRequest.
     */
    public function create(User $user): bool
    {
        return $user->isRole(...self::PERAN_PENGAJU);
    }

    /**
     * Pemilik pengajuan — siapa pun perannya — boleh melihat pengajuannya.
     * Peran sengaja tidak dibatasi 'pegawai' lagi agar verifikator/PPK/
     * bendahara/PPSPM dapat menengok pengajuan yang ia buat sendiri.
     */
    public function view(User $user, Pengajuan $pengajuan): bool
    {
        return $pengajuan->id_user === $user->id_user;
    }

    public function update(User $user, Pengajuan $pengajuan): bool
    {
        return $this->view($user, $pengajuan)
            && $pengajuan->status?->kode_status === 'REVISI';
    }
}
