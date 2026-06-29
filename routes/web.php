<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\KurirWebController;
use App\Http\Controllers\JenisSampahWebController;
use App\Http\Controllers\JadwalWebController;
use App\Http\Controllers\NasabahWebController;
use App\Http\Controllers\SetorSampahWebController;
use App\Http\Controllers\TarikTunaiWebController;
use App\Http\Controllers\MasterJadwalRutinController;
use App\Http\Controllers\DlhDashboardController;
use App\Http\Controllers\DlhBankSampahWebController;
use App\Http\Controllers\DlhAduanWebController;

/**
 * ======================================================================
 * RUTE DARURAT: Untuk membersihkan cache tanpa perlu akses SSH
 * ======================================================================
 */
Route::get('/system-refresh-6285', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return "✅ Cache berhasil dibersihkan! Aplikasi web sudah diperbarui.";
});

// 🔥 RUTE UNTUK MIGRASI DATABASE DARI WEB
Route::get('/up-db', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return "✅ Database berhasil diperbarui! Silakan cek fitur jadwal rutin Anda.";
    } catch (\Exception $e) {
        return "❌ Gagal migrasi: " . $e->getMessage();
    }
});

// Rute untuk menerima setup PIN dari Flutter
Route::post('/setup-pin', function (\Illuminate\Http\Request $request) {
    // Ini contoh logika sederhana untuk tes koneksi dari Flutter
    return response()->json([
        'status' => 'success',
        'message' => 'Rute setup-pin berhasil dihubungkan ke Laravel!'
    ], 200);
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AdminWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminWebController::class, 'login']);
Route::post('/logout', [AdminWebController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('admin')->middleware('auth')->group(function () {

    Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('admin.dashboard');

    Route::resource('kurir', KurirWebController::class);
    Route::resource('jenis-sampah', JenisSampahWebController::class);
    Route::resource('jadwal', JadwalWebController::class);

    Route::resource('master-jadwal', MasterJadwalRutinController::class)->names([
        'index'   => 'master-jadwal.index',
        'create'  => 'master-jadwal.create',
        'store'   => 'master-jadwal.store',
        'destroy' => 'master-jadwal.destroy',
    ]);

    Route::prefix('setor-sampah')->group(function () {
        Route::get('/', [SetorSampahWebController::class, 'index'])->name('admin.setor.index');
        Route::get('/manual/{id}', [SetorSampahWebController::class, 'formManual'])->name('admin.setor.form-manual');
        Route::post('/manual/{id}', [SetorSampahWebController::class, 'prosesManual'])->name('admin.setor.proses-manual');
        Route::get('/{id}', [SetorSampahWebController::class, 'show'])->name('admin.setor.show');
        Route::post('/{id}/status', [SetorSampahWebController::class, 'updateStatus'])->name('admin.setor.updateStatus');
        Route::delete('/{id}', [SetorSampahWebController::class, 'destroy'])->name('admin.setor.destroy');
    });

    Route::prefix('nasabah')->group(function () {
        Route::get('/', [NasabahWebController::class, 'index'])->name('admin.nasabah.index');
        Route::get('/{id}/print-qr', [NasabahWebController::class, 'printQr'])->name('admin.nasabah.print-qr');
        Route::get('/{id}', [NasabahWebController::class, 'show'])->name('admin.nasabah.show');
        Route::post('/{id}/approve', [NasabahWebController::class, 'approve'])->name('admin.nasabah.approve');
        Route::post('/{id}/status', [NasabahWebController::class, 'updateStatus'])->name('admin.nasabah.updateStatus');
        Route::delete('/{id}', [NasabahWebController::class, 'destroy'])->name('admin.nasabah.destroy');
    });

    Route::prefix('tarik-tunai')->group(function () {
        Route::get('/', [TarikTunaiWebController::class, 'index'])->name('admin.tarik-tunai.index');
        Route::post('/{id}/approve', [TarikTunaiWebController::class, 'approve'])->name('admin.tarik-tunai.approve');
        Route::post('/{id}/reject', [TarikTunaiWebController::class, 'reject'])->name('admin.tarik-tunai.reject');
    });
    Route::get('/riwayat-penarikan', [TarikTunaiWebController::class, 'riwayat'])->name('admin.tarik-tunai.riwayat');

});

Route::prefix('dlh')->middleware(['auth', 'admin_dlh'])->group(function () {

    Route::get('/dashboard', [DlhDashboardController::class, 'index'])->name('dlh.dashboard');

    Route::prefix('bank-sampah')->name('dlh.bank-sampah.')->group(function () {
        Route::resource('/', DlhBankSampahWebController::class)->parameters(['' => 'id']);
        Route::post('/{id}/approve', [DlhBankSampahWebController::class, 'approve'])
            ->name('approve');
    });

    Route::prefix('aduan')->group(function () {
        Route::get('/', [DlhAduanWebController::class, 'index'])->name('dlh.aduan.index');
        Route::get('/{id}', [DlhAduanWebController::class, 'show'])->name('dlh.aduan.show');
        Route::put('/{id}', [DlhAduanWebController::class, 'update'])->name('dlh.aduan.update');
    });

});
