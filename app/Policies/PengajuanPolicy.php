<?php

namespace App\Policies;

use App\Models\Pengajuan;
use App\Models\User;

class PengajuanPolicy
{
    public function view(User $user, Pengajuan $pengajuan): bool
    {
        return $user->isRole('pegawai') && $pengajuan->id_user === $user->id_user;
    }

    public function update(User $user, Pengajuan $pengajuan): bool
    {
        return $this->view($user, $pengajuan)
            && $pengajuan->status?->kode_status === 'REVISI';
    }
}
