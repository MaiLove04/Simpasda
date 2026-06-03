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



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. ROUTE PUBLIC (Tanpa Login / Tanpa Token)
// ==========================================

Route::get('/test', function () {
    return response()->json(['message' => 'API Bank Sampah ASRI Berjalan Lancar']);
});

// Autentikasi Utama
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/create-admin', [AuthController::class, 'createAdmin']);

// Fitur QR Code & Scanner Nasabah (Dipanggil Aplikasi Kurir)
Route::get('/nasabah/qrcode/{kode}', [UserController::class, 'scanQr']);
Route::get('/bank-sampahs', [BankSampahController::class, 'index']);

// Alur Setor Sampah dari Flutter Kurir
Route::post('/setor-sampah', [SetorSampahController::class, 'store']);
Route::get('/setor-sampah', [SetorSampahController::class, 'index']);

// Fitur Utama Kurir (Dashboard & Jadwal Lapangan)
Route::get('/dashboard-kurir/{id}', [KurirController::class, 'dashboard_kurir']);
Route::get('/kurir/jadwal/{id}', [JadwalPenjemputanController::class, 'jadwalKurir']);

// Aksi Kurir: Mengubah status penjemputan dari 'terjadwal' menjadi 'proses'
Route::put('/jadwal-penjemputan/{id}/mulai', [JadwalPenjemputanController::class, 'mulaiJemput']);

//riwayat setor sampah kurir
Route::get('/riwayat-kurir/{kurir_id}', [SetorSampahController::class, 'getRiwayatTotal']);
// Fitur Berat IoT (Simulasi Data Berat dari Alat IoT)
Route::get('/berat-timbangan-iot', [SetorSampahController::class, 'getBeratIot']);
//data total sampah kurir dashboard
// Jalur API untuk menyuplai data counter dashboard kurir secara real-time
Route::get('/dashboard-kurir/{kurir_id}', [SetorSampahController::class, 'getDashboardKurir']);

Route::get('/dashboard-nasabah/{user_id}', [UserController::class, 'dashboard_nasabah']);

// Jalur API ketika nasabah melakukan klik request jemput sampah massal
Route::post('/request-penjemputan', [SetorSampahController::class, 'requestPenjemputan']);

// Rute untuk kurir mengambil otomatis jenis sampah bawaan request nasabah
Route::get('/request-detail/{nasabah_id}', [SetorSampahController::class, 'showRequestDetail']);

//Fitur Berat IoT (Simulasi Data Berat dari Alat IoT)
// Endpoint yang ditembak oleh alat IoT (Menggunakan POST)
Route::post('/update-berat-iot', [IotTimbanganController::class, 'updateBerat']);

// Endpoint yang ditembak oleh Flutter Mai (Menggunakan GET)
Route::get('/berat-timbangan-iot', [IotTimbanganController::class, 'getBeratTerakhir']);



// ==========================================
// 2. ROUTE FOR WEB ADMIN (Pengelolaan Jenis Sampah)
// ==========================================
Route::resource('/admin/jenis-sampah', JenisSampahWebController::class);
Route::apiResource('jenis-sampah', JenisSampahController::class);
Route::put('/jenis-sampahs/{id}', [JenisSampahController::class, 'update']);
Route::delete('/jenis-sampahs/{id}', [JenisSampahController::class, 'destroy']);


// ==========================================
// 3. ROUTE PROTECTED (Wajib Menggunakan Bearer Token / Sanctum)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // Identitas & Logout Aman
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/transaksi', [TransaksiController::class, 'store']);
    Route::get('/barcode/nasabah/{id}', [BarcodeController::class, 'barcodeNasabah']);


    // 🟢 TAMBAHKAN DUA BARIS BARU INI DI SINI:
    Route::get('/nasabah', [UserController::class, 'index']); 
    Route::get('/dashboard-stats', [UserController::class, 'getDashboardStats']);
    Route::get('/kurir', [KurirController::class, 'index']);

    // Identitas & Logout Aman (Bawaan kodemu yang sudah ada)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/transaksi', [TransaksiController::class, 'store']);

    // ------------------------------------------
    // MIDDLEWARE: ADMIN DLH (Dinas Lingkungan Hidup)
    // ------------------------------------------
    Route::middleware('admin_dlh')->group(function () {
        Route::prefix('bank-sampah')->group(function () {
            Route::get('/', [BankSampahController::class, 'index']);
            Route::post('/', [BankSampahController::class, 'store']);
            Route::get('/{id}', [BankSampahController::class, 'show']);
            Route::post('/{id}/approve', [BankSampahController::class, 'approve']);
            Route::delete('/{id}', [BankSampahController::class, 'destroy']);
        });
    });

    // ------------------------------------------
    // MIDDLEWARE: ADMIN BANK SAMPAH (Lokasi)
    // ------------------------------------------
    Route::middleware('admin_bank')->group(function () {
        Route::post('/nasabah/{id}/approve', [UserController::class, 'approveNasabah']);
        Route::post('/kurir', [UserController::class, 'createKurir']);
        Route::post('/jadwal-penjemputan', [JadwalPenjemputanController::class, 'store']);
    });

    

});