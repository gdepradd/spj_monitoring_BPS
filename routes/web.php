<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Pegawai\DashboardController as PegawaiDashboardController;
use App\Http\Controllers\Pegawai\PengajuanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    $role = auth()->user()->loadMissing('role')->role?->nama_role;

    return redirect(match ($role) {
        'pegawai' => '/pegawai/dashboard',
        'verifikator' => '/verifikator/dashboard',
        'ppk' => '/ppk/dashboard',
        'bendahara' => '/bendahara/dashboard',
        'ppspm' => '/ppspm/dashboard',
        'admin' => '/admin/users',
        default => '/login',
    });
});

Route::middleware(['auth', 'role:pegawai'])
    ->prefix('pegawai')
    ->name('pegawai.')
    ->group(function () {
        Route::get('/dashboard', [PegawaiDashboardController::class, 'index'])->name('dashboard');

        // Harus didefinisikan sebelum /pengajuan/{pengajuan} agar "riwayat" tidak terbaca sebagai ID.
        Route::get('/pengajuan/riwayat', [PengajuanController::class, 'riwayat'])
            ->name('pengajuan.riwayat');

        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/create', [PengajuanController::class, 'create'])->name('pengajuan.create');
        Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
        Route::get('/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])->name('pengajuan.show');
        Route::get('/pengajuan/{pengajuan}/edit', [PengajuanController::class, 'edit'])->name('pengajuan.edit');
        Route::put('/pengajuan/{pengajuan}', [PengajuanController::class, 'update'])->name('pengajuan.update');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });

require __DIR__ . '/auth.php';
