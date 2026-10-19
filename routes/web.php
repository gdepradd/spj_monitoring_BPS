<?php

use App\Http\Controllers\Admin\TimController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Pegawai\DashboardController as PegawaiDashboardController;
use App\Http\Controllers\Pegawai\PengajuanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Verifikator\DashboardController;
use App\Http\Controllers\Verifikator\PengajuanController as VerifikatorPengajuanController;
use App\Http\Controllers\Pencairan\PpkController;
use App\Http\Controllers\Pencairan\BendaharaController;
use App\Http\Controllers\Pencairan\PpspmController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\Auth\PasswordController;

// GANTI PASSWORD — milik akun sendiri, terbuka untuk semua role.
Route::middleware('auth')->group(function () {
    Route::get('/akun/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/akun/password', [PasswordController::class, 'update'])->name('password.update');
});

// DETAIL RIWAYAT (dipakai semua role: verifikator s.d. pencairan)
Route::middleware(['auth', 'role:pegawai,verifikator,ppk,bendahara,ppspm,admin'])
    ->get('/riwayat/pengajuan/{id}', [RiwayatController::class, 'show'])
    ->name('riwayat.detail');

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
    // Pembayaran lanjutan untuk pengajuan yang dicairkan bertahap.
    Route::post('/pengajuan/{id}/bayar', [BendaharaController::class, 'bayar'])->name('pengajuan.bayar');
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
        'admin' => '/admin/monitoring',
        default => '/login',
    });
});

// DASHBOARD PEGAWAI — tetap khusus pegawai karena memuat panel pengajuan satu tim.
// Role lain memakai dashboard perannya masing-masing.
Route::middleware(['auth', 'role:pegawai'])
    ->prefix('pegawai')
    ->name('pegawai.')
    ->group(function () {
        Route::get('/dashboard', [PegawaiDashboardController::class, 'index'])->name('dashboard');
    });

// PENGAJUAN PRIBADI — terbuka untuk semua role pelaksana, bukan hanya pegawai.
// Verifikator/PPK/Bendahara/PPSPM tetap menjalankan tugas pemeriksaannya di grup
// rutenya sendiri; rute di sini hanya menyangkut pengajuan milik akun sendiri,
// dan setiap aksi pada satu pengajuan dijaga PengajuanPolicy (cek kepemilikan).
// Admin tidak disertakan karena perannya read-only atas alur pengajuan.
Route::middleware(['auth', 'role:pegawai,verifikator,ppk,bendahara,ppspm'])
    ->prefix('pengajuan')
    ->name('pengajuan.')
    ->group(function () {
        // Harus didefinisikan sebelum /{pengajuan} agar "create" tidak
        // terbaca sebagai ID pengajuan.
        Route::get('/create', [PengajuanController::class, 'create'])->name('create');

        Route::get('/', [PengajuanController::class, 'index'])->name('index');
        Route::post('/', [PengajuanController::class, 'store'])->name('store');
        Route::get('/{pengajuan}', [PengajuanController::class, 'show'])->name('show');
        Route::get('/{pengajuan}/edit', [PengajuanController::class, 'edit'])->name('edit');
        Route::put('/{pengajuan}', [PengajuanController::class, 'update'])->name('update');
    });

// ADMIN (super admin): monitoring menyeluruh + manajemen user.
// Seluruh rute monitoring hanya GET — admin memantau, tidak mengubah alur pengajuan.
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/monitoring', [MonitoringController::class, 'dashboard'])->name('monitoring.dashboard');
        Route::get('/monitoring/aktivitas', [MonitoringController::class, 'aktivitas'])->name('monitoring.aktivitas');
        Route::get('/monitoring/pengajuan', [MonitoringController::class, 'pengajuan'])->name('monitoring.pengajuan');
        Route::get('/monitoring/pengajuan/{id}', [MonitoringController::class, 'show'])->name('monitoring.show');

        Route::resource('users', UserController::class)->except('show');
        Route::resource('tim', TimController::class)->except('show')->parameters(['tim' => 'tim']);
    });
Route::middleware(['auth', 'role:verifikator'])->prefix('verifikator')->name('verifikator.')->group(function () {
    Route::redirect('/dashboard', '/verifikator/pengajuan')->name('dashboard');
    Route::get('/pengajuan', [VerifikatorPengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{id}', [VerifikatorPengajuanController::class, 'show'])->name('pengajuan.show');
    Route::post('/pengajuan/{id}/keputusan', [VerifikatorPengajuanController::class, 'keputusan'])->name('pengajuan.keputusan');
    Route::get('/riwayat', [VerifikatorPengajuanController::class, 'riwayat'])->name('riwayat');
});
require __DIR__ . '/auth.php';
