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
 * 🛠️ RUTE UTILITY & MAINTENANCE (Akses via URL Browser)
 * ======================================================================
 */

// Menyegarkan seluruh sistem cache
Route::get('/system-refresh-6285', function () {
    Artisan::call('optimize:clear');
    return "✅ Cache berhasil dibersihkan! Aplikasi web sudah diperbarui.";
});

// Jalankan migrasi database otomatis dari web
Route::get('/up-db', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return "✅ Database berhasil diperbarui! Silakan cek fitur jadwal rutin Anda.";
    } catch (\Exception $e) {
        return "❌ Gagal migrasi: " . $e->getMessage();
    }
});

// Endpoint tes koneksi setup PIN dari Flutter
Route::post('/setup-pin', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'status' => 'success',
        'message' => 'Rute setup-pin berhasil dihubungkan ke Laravel!'
    ], 200);
});


/**
 * ======================================================================
 * 🔐 AUTHENTICATION
 * ======================================================================
 */
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AdminWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminWebController::class, 'login']);
Route::post('/logout', [AdminWebController::class, 'logout'])->middleware('auth')->name('logout');


/**
 * ======================================================================
 * 🏢 AREA ADMIN BANK SAMPAH
 * ======================================================================
 */
Route::prefix('admin')->middleware('auth')->group(function () {

    Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('admin.dashboard');

    Route::resource('kurir', KurirWebController::class);
    Route::resource('jenis-sampah', JenisSampahWebController::class);
    Route::resource('jadwal', JadwalWebController::class);

    // Rute Force Sync Manual penutupan jadwal rutin
    Route::post('/master-jadwal/generate', [MasterJadwalRutinController::class, 'generate'])->name('master-jadwal.generate');

    // CRUD Master Jadwal Rutin Nasabah
    Route::resource('master-jadwal', MasterJadwalRutinController::class)->names([
        'index'   => 'master-jadwal.index',
        'create'  => 'master-jadwal.create',
        'store'   => 'master-jadwal.store',
        'destroy' => 'master-jadwal.destroy',
    ]);

    // ♻️ Manajemen Transaksi Setor Sampah (Admin hanya monitoring & rekap manual)
    Route::prefix('setor-sampah')->group(function () {
        $controller = SetorSampahWebController::class;
        Route::get('/', [$controller, 'index'])->name('admin.setor.index');
        Route::get('/{id}', [$controller, 'show'])->name('admin.setor.show');
        Route::delete('/{id}', [$controller, 'destroy'])->name('admin.setor.destroy');
        
        // Input manual oleh admin jika nasabah langsung datang ke lokasi bank sampah
        Route::get('/manual/{id}', [$controller, 'formManual'])->name('admin.setor.form-manual');
        Route::post('/manual/{id}', [$controller, 'prosesManual'])->name('admin.setor.proses-manual');
        
        // 🔥 PERBAIKAN: Rute updateStatus gantung dihapus karena transaksi berstatus 'selesai' otomatis via Kurir.
    });

    // 📋 Manajemen Data Nasabah
    Route::prefix('nasabah')->group(function () {
        $controller = NasabahWebController::class;
        Route::get('/', [$controller, 'index'])->name('admin.nasabah.index');
        Route::get('/{id}/print-qr', [$controller, 'printQr'])->name('admin.nasabah.print-qr');
        Route::get('/{id}', [$controller, 'show'])->name('admin.nasabah.show');
        Route::delete('/{id}', [$controller, 'destroy'])->name('admin.nasabah.destroy');
        
        // 🔄 UBAH KE PATCH: Update status persetujuan akun nasabah baru
        Route::patch('/{id}/approve', [$controller, 'approve'])->name('admin.nasabah.approve');
        Route::patch('/{id}/status', [$controller, 'updateStatus'])->name('admin.nasabah.updateStatus');
    });

    // 💰 Manajemen Keuangan / Tarik Tunai
    Route::prefix('tarik-tunai')->group(function () {
        $controller = TarikTunaiWebController::class;
        Route::get('/', [$controller, 'index'])->name('admin.tarik-tunai.index');
        
        // 🔄 UBAH KE PATCH: Verifikasi penarikan uang saldo nasabah
        Route::patch('/{id}/approve', [$controller, 'approve'])->name('admin.tarik-tunai.approve');
        Route::patch('/{id}/reject', [$controller, 'reject'])->name('admin.tarik-tunai.reject');
    });
    Route::get('/riwayat-penarikan', [TarikTunaiWebController::class, 'riwayat'])->name('admin.tarik-tunai.riwayat');

});


/**
 * ======================================================================
 * 🏛️ AREA ADMIN DINAS LINGKUNGAN HIDUP (DLH)
 * ======================================================================
 */
Route::prefix('dlh')->middleware(['auth', 'admin_dlh'])->group(function () {

    Route::get('/dashboard', [DlhDashboardController::class, 'index'])->name('dlh.dashboard');

    Route::prefix('bank-sampah')->name('dlh.bank-sampah.')->group(function () {
        Route::resource('/', DlhBankSampahWebController::class)->parameters(['' => 'id']);
        
        // 🔄 UBAH KE PATCH: Dinas melakukan approval izin Bank Sampah Unit
        Route::patch('/{id}/approve', [DlhBankSampahWebController::class, 'approve'])->name('approve');
    });

    Route::prefix('aduan')->group(function () {
        $controller = DlhAduanWebController::class;
        Route::get('/', [$controller, 'index'])->name('dlh.aduan.index');
        Route::get('/{id}', [$controller, 'show'])->name('dlh.aduan.show');
        Route::put('/{id}', [$controller, 'update'])->name('dlh.aduan.update'); // Tetap PUT karena update full data aduan
    });

});