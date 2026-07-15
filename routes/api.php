<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KurirController;
use App\Http\Controllers\BankSampahController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\SetorSampahController;
use App\Http\Controllers\JenisSampahController;
use App\Http\Controllers\JadwalPenjemputanController;
use App\Http\Controllers\JenisSampahWebController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\Api\IotTimbanganController;
use App\Http\Controllers\AduanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Api\TarikTunaiController;

// --- PUBLIC ROUTES (Tidak Perlu Token) ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/create-admin', [AuthController::class, 'createAdmin']);
Route::get('/bank-sampahs', [BankSampahController::class, 'index']);
Route::post('/update-berat-iot', [IotTimbanganController::class, 'updateBerat']); // IoT biasanya tidak pakai token

// --- PROTECTED ROUTES (Wajib Login/Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Nasabah
    Route::get('/nasabah', [UserController::class, 'index']);
    Route::get('/dashboard-nasabah', [UserController::class, 'dashboard_nasabah']);
    Route::get('/barcode/nasabah', [BarcodeController::class, 'barcodeNasabah']);
    Route::get('/request-detail', [SetorSampahController::class, 'showRequestDetail']);
    Route::post('/request-penjemputan', [SetorSampahController::class, 'requestPenjemputan']);
    Route::post('/tarik-tunai', [UserController::class, 'tarikTunai']);
    Route::get('/aduan/riwayat', [AduanController::class, 'riwayat']);

    // Kurir & Jadwal
    Route::get('/dashboard-kurir', [KurirController::class, 'dashboard_kurir']);
    Route::get('/kurir/jadwal', [JadwalPenjemputanController::class, 'jadwalKurir']);
    Route::get('/nasabah/jadwal', [JadwalPenjemputanController::class, 'jadwalNasabah']);
    Route::put('/jadwal-penjemputan/{id}/mulai', [JadwalPenjemputanController::class, 'mulaiJemput']);
    Route::put('/jadwal-penjemputan/{id}/batal', [JadwalPenjemputanController::class, 'batalJemput']);
    Route::get('/riwayat-kurir', [SetorSampahController::class, 'getRiwayatTotal']);

    // Notifikasi
    Route::get('/notifikasi-kurir', [NotifikasiController::class, 'getNotifikasiKurir']);
    Route::get('/notifikasi-nasabah', [NotifikasiController::class, 'getNotifikasiNasabah']);
    Route::post('/notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead']);

    // Admin & Umum
    Route::get('/dashboard-stats', [UserController::class, 'getDashboardStats']);
    Route::post('/transaksi', [TransaksiController::class, 'store']);
    Route::post('/aduan', [AduanController::class, 'store']);
    Route::get('/berat-timbangan-iot', [IotTimbanganController::class, 'getBeratTerakhir']);

    // Admin Bank Sampah
    Route::middleware('admin_bank')->group(function () {
        Route::post('/nasabah/{id}/approve', [UserController::class, 'approveNasabah']);
        Route::post('/jadwal-penjemputan', [JadwalPenjemputanController::class, 'store']);
        
        // Hanya route selain GET (POST, PUT, DELETE) yang butuh admin_bank
        Route::apiResource('jenis-sampah', JenisSampahController::class)->except(['index', 'show']);
    });
    
    // Semua user (termasuk nasabah) bisa melihat jenis sampah
    Route::get('jenis-sampah', [JenisSampahController::class, 'index']);
    Route::get('jenis-sampah/{jenis_sampah}', [JenisSampahController::class, 'show']);

    // Admin DLH
    Route::middleware('admin_dlh')->group(function () {
        Route::prefix('bank-sampah')->group(function () {
            Route::post('/', [BankSampahController::class, 'store']);
            Route::post('/{id}/approve', [BankSampahController::class, 'approve']);
        });
    });
});