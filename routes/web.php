<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\KurirWebController;
use App\Http\Controllers\JenisSampahWebController;
use App\Http\Controllers\JadwalWebController;
use App\Http\Controllers\NasabahWebController;
use App\Http\Controllers\SetorSampahWebController; 
use App\Http\Controllers\MasterJadwalRutinController;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::prefix('admin')->group(function () {

    // Login (Tanpa perlu auth)
    Route::get(
        '/login',
        [AdminWebController::class, 'showLogin']
    );

    Route::post(
        '/login',
        [AdminWebController::class, 'login']
    );

    // Protected (Hanya bisa diakses jika sudah login admin)
    Route::middleware('auth')->group(function () {

        Route::get(
            '/dashboard',
            [AdminWebController::class, 'dashboard']
        );

        Route::post(
            '/logout',
            [AdminWebController::class, 'logout']
        );

        Route::resource(
            'kurir',
            KurirWebController::class
        );

        Route::resource(
            'jenis-sampah',
            JenisSampahWebController::class
        );

        Route::resource(
            'jadwal',
            JadwalWebController::class
        );

        // ========================================================
        // MASTER JADWAL RUTIN (SINKRON DI SINI)
        // ========================================================
        Route::resource('master-jadwal', MasterJadwalRutinController::class)->names([
            'index'   => 'master-jadwal.index',
            'create'  => 'master-jadwal.create',
            'store'   => 'master-jadwal.store',
            'destroy' => 'master-jadwal.destroy',
        ]);

        // ========================================================
        // TRANSAKSI SETOR SAMPAH (WEB ADMIN)
        // ========================================================
        Route::get(
            '/setor-sampah',
            [SetorSampahWebController::class, 'index']
        )->name('admin.setor.index');

        Route::get(
            '/setor-sampah/{id}',
            [SetorSampahWebController::class, 'show']
        )->name('admin.setor.show');

        Route::post(
            '/setor-sampah/{id}/status',
            [SetorSampahWebController::class, 'updateStatus']
        )->name('admin.setor.updateStatus');

        Route::delete(
            '/setor-sampah/{id}',
            [SetorSampahWebController::class, 'destroy']
        )->name('admin.setor.destroy');

        // 🔥 FITUR BARU: Setor Sampah Manual Langsung via Loket Web Admin
        Route::get(
            '/setor-sampah/manual/{id}',
            [SetorSampahWebController::class, 'formManual']
        )->name('admin.setor.form-manual');

        Route::post(
            '/setor-sampah/manual/{id}',
            [SetorSampahWebController::class, 'prosesManual']
        )->name('admin.setor.proses-manual');

        // ========================================================
        // NASABAH MANAGEMENT (SUDAH DISINKRONKAN DENGAN FITUR CETAK)
        // ========================================================
        Route::get(
            '/nasabah',
            [NasabahWebController::class, 'index']
        )->name('admin.nasabah.index');

        // 🖨️ FITUR BARU: Rute Cetak QR Code Siap Tempel di Rumah Nasabah
        Route::get(
            '/nasabah/{id}/print-qr',
            [NasabahWebController::class, 'printQr']
        )->name('admin.nasabah.print-qr');

        Route::get(
            '/nasabah/{id}',
            [NasabahWebController::class, 'show']
        );

        Route::post(
            '/nasabah/{id}/approve',
            [NasabahWebController::class, 'approve']
        );

        Route::delete(
            '/nasabah/{id}',
            [NasabahWebController::class, 'destroy']
        );

        // Status nasabah
        Route::post(
            '/nasabah/{id}/status',
            [NasabahWebController::class, 'updateStatus']
        );

        // ========================================================
        // MENU TARIK TUNAI & RIWAYAT (SUDAH DISINKRONKAN)
        // ========================================================
        Route::get('/tarik-tunai', [NasabahWebController::class, 'indexTarikTunai'])->name('admin.tarik-tunai.index');
        Route::get('/tarik-tunai/{id}', [NasabahWebController::class, 'tarikTunai'])->name('admin.tarik-tunai.form');
        Route::post('/tarik-tunai/{id}', [NasabahWebController::class, 'prosesTarikTunai'])->name('admin.tarik-tunai.proses');
        
        // Jalur halaman riwayat penarikan saldo nasabah
        Route::get('/riwayat-penarikan', [NasabahWebController::class, 'riwayatPenarikan'])->name('admin.tarik-tunai.riwayat');
        
    });
});