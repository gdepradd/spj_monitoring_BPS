<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Pegawai\DashboardController as PegawaiDashboardController;
use App\Http\Controllers\Pegawai\PengajuanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Verifikator\DashboardController;
use App\Http\Controllers\Verifikator\PengajuanController as VerifikatorPengajuanController;
use App\Http\Controllers\Pencairan\PpkController;
use App\Http\Controllers\Pencairan\BendaharaController;
use App\Http\Controllers\Pencairan\PpspmController;

// PPK
Route::middleware(['auth', 'role:ppk'])->prefix('ppk')->name('ppk.')->group(function () {
    Route::get('/dashboard', [PpkController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengajuan', [PpkController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{id}', [PpkController::class, 'show'])->name('pengajuan.show');
    Route::post('/pengajuan/{id}/terbitkan-spm', [PpkController::class, 'terbitkanSpm'])->name('pengajuan.terbitkan-spm');
    Route::get('/riwayat', [PpkController::class, 'riwayat'])->name('riwayat');
});

// BENDAHARA
Route::middleware(['auth', 'role:bendahara'])->prefix('bendahara')->name('bendahara.')->group(function () {
    Route::get('/dashboard', [BendaharaController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengajuan', [BendaharaController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{id}', [BendaharaController::class, 'show'])->name('pengajuan.show');
    Route::post('/pengajuan/{id}/ajukan', [BendaharaController::class, 'ajukan'])->name('pengajuan.ajukan');
    Route::post('/pengajuan/{id}/konfirmasi', [BendaharaController::class, 'konfirmasi'])->name('pengajuan.konfirmasi');
    Route::get('/riwayat', [BendaharaController::class, 'riwayat'])->name('riwayat');
});

// PPSPM
Route::middleware(['auth', 'role:ppspm'])->prefix('ppspm')->name('ppspm.')->group(function () {
    Route::get('/dashboard', [PpspmController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengajuan', [PpspmController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{id}', [PpspmController::class, 'show'])->name('pengajuan.show');
    Route::post('/pengajuan/{id}/ajukan-kemenkeu', [PpspmController::class, 'ajukanKemenkeu'])->name('pengajuan.ajukan-kemenkeu');
    Route::get('/riwayat', [PpspmController::class, 'riwayat'])->name('riwayat');
});
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
Route::middleware(['auth', 'role:verifikator'])->prefix('verifikator')->name('verifikator.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pengajuan', [VerifikatorPengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{id}', [VerifikatorPengajuanController::class, 'show'])->name('pengajuan.show');
    Route::post('/pengajuan/{id}/keputusan', [VerifikatorPengajuanController::class, 'keputusan'])->name('pengajuan.keputusan');
    Route::get('/riwayat', [VerifikatorPengajuanController::class, 'riwayat'])->name('riwayat');
});
require __DIR__ . '/auth.php';
