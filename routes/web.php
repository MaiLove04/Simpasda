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

        // Halaman setor sampah untuk web admin
        Route::get(
            '/setor-sampah',
            [SetorSampahWebController::class, 'index']
        )->name('admin.setor.index');

        // ========================================================
        // NASABAH MANAGEMENT (SUDAH DISINKRONKAN DENGAN FITUR CETAK)
        // ========================================================
        Route::get(
            '/nasabah',
            [NasabahWebController::class, 'index']
        )->name('admin.nasabah.index'); // Tambah nama rute index

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

    });
});