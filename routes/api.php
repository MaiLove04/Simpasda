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
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\Api\IotTimbanganController;
use App\Http\Controllers\AduanController;
use App\Http\Controllers\Api\TarikTunaiController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\NotifikasiController;

// Public Routes
Route::get('/test', fn() => response()->json(['message' => 'API Bank Sampah ASRI Berjalan Lancar']));

// Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/create-admin', [AuthController::class, 'createAdmin']); 

// OTP
Route::post('/otp/send', [OtpController::class, 'sendOtp']);
Route::post('/otp/reset-password', [OtpController::class, 'verifyOtpAndReset']);

// Bank Sampah
Route::get('/bank-sampah', [BankSampahController::class, 'index']);

// Nasabah
Route::get('/nasabah/qrcode/{kode}', [UserController::class, 'scanQr']);
Route::get('/dashboard-nasabah/{user_id}', [UserController::class, 'dashboard_nasabah']);

// =========================================================================
// 📋 KURIR (PUBLIC AREA - AKSES MUDAH DARI APLIKASI FLUTTER)
// =========================================================================
Route::get('/dashboard-kurir/{id}', [KurirController::class, 'dashboard_kurir']);
Route::get('/kurir/jadwal/{id}', [JadwalPenjemputanController::class, 'jadwalKurir']); 
Route::get('/dashboard-kurir-counter/{kurir_id}', [SetorSampahController::class, 'getDashboardKurir']);
Route::get('/riwayat-kurir/{kurir_id}', [SetorSampahController::class, 'getRiwayatTotal']);

// 🔥 SCAN QR (Tetap POST sesuai kodingan Flutter)
Route::post('/kurir/scan-qr', [KurirController::class, 'scanQrNasabah']);
Route::get(
    '/jadwal/scan/{id}',
    [JadwalPenjemputanController::class,'scanQr']
);

// 🔄 UBAH KE PATCH: Mengubah Status Alur Penjemputan
// Proses alur jemput oleh kurir lapangan
Route::patch('/jadwal-penjemputan/{id}/mulai', [JadwalPenjemputanController::class, 'mulaiJemput']);
Route::patch('/jadwal-penjemputan/{id}/batal', [JadwalPenjemputanController::class, 'batalJemput']);

// =========================================================================
// ♻️ PROSES SETOR / INPUT DATA TIMBANGAN OLEH KURIR (UBAH KE PATCH)
// =========================================================================



// 1. Kurir isi Jenis & Berat untuk JADWAL dari ADMIN
Route::post('/setor-sampah/jadwal-admin/{id}', [SetorSampahController::class, 'setorJadwalAdmin']);

// 2. Kurir update Berat untuk REQUEST dari NASABAH
Route::patch('/setor-sampah/request-nasabah/{setor_sampah_id}', [SetorSampahController::class, 'setorRequestNasabah']);

// Route pendukung transaksi setor sampah
Route::get('/setor-sampah', [SetorSampahController::class, 'index']); 
Route::post('/request-penjemputan', [SetorSampahController::class, 'requestPenjemputan']);
Route::get('/request-detail/{nasabah_id}', [SetorSampahController::class, 'showRequestDetail']);

// Notifikasi

// Aduan
Route::post('/aduan', [AduanController::class, 'store']);
Route::get('/aduan/riwayat/{user_id}', [AduanController::class, 'riwayat']);

// Jenis Sampah
Route::apiResource('jenis-sampah', JenisSampahController::class);

// IoT
Route::post('/update-berat-iot', [IotTimbanganController::class, 'updateBerat']);
Route::get('/berat-timbangan-iot', [IotTimbanganController::class, 'getBeratTerakhir']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/notifikasi-kurir', [NotifikasiController::class, 'getNotifikasiKurir']);
    Route::get('/notifikasi-nasabah', [NotifikasiController::class, 'getNotifikasiNasabah']);
    Route::post('/notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead']);

    Route::get('/barcode/nasabah/{id}', [BarcodeController::class, 'barcodeNasabah']);
    Route::get('/nasabah', [UserController::class, 'index']);
    Route::get('/dashboard-stats', [UserController::class, 'getDashboardStats']); 
    Route::apiResource('kurir', KurirController::class);
    Route::post('/kurir/{id}', [KurirController::class, 'update']); 
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/transaksi', [TransaksiController::class, 'store']);

    // Fitur Tarik Tunai
    Route::get('/tarik-tunai', [TarikTunaiController::class, 'index']);
    Route::get('/tarik-tunai/riwayat-lengkap', [TarikTunaiController::class, 'index']);
    Route::post('/tarik-tunai', [UserController::class, 'tarikTunai']);
    // Route::post('/tarik-tunai', [UserContr::class, 'store']);
    Route::patch('/tarik-tunai/{id}/approve', [TarikTunaiController::class, 'approve']);
    Route::patch('/tarik-tunai/{id}/reject', [TarikTunaiController::class, 'reject']);

    // Fitur Penjadwalan Nasabah
    Route::get('/nasabah/jadwal/{id}', [JadwalPenjemputanController::class, 'jadwalNasabah']);
    Route::post('/jadwal-penjemputan', [JadwalPenjemputanController::class, 'store']);

    Route::post('/setup-pin', [UserController::class, 'setupPin']);

    // Middleware Admin DLH
    Route::middleware('admin_dlh')->prefix('dlh')->name('dlh.')->group(function () {
        Route::prefix('bank-sampah')->name('bank-sampah.')->group(function () {
            Route::apiResource('/', BankSampahController::class)->except(['index'])->parameters(['' => 'id']);
            Route::post('/{id}/approve', [BankSampahController::class, 'approve']);
        });
    });

    // Middleware Admin Bank Sampah
    Route::middleware('admin_bank')->group(function () {
        Route::post('/nasabah/{id}/approve', [UserController::class, 'approveNasabah']);
    });

    
}); // Kurung penutup middleware utama berada di sini sekarang
