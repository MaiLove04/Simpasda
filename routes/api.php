<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankSampahController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\SetorSampahController;
use App\Http\Controllers\JenisSampahController;
use App\Http\Controllers\KurirController;
use App\Http\Controllers\JadwalPenjemputanController;
use App\Http\Controllers\JenisSampahWebController;



// ================= TEST =================
Route::get('/test', function () {
    return response()->json(['message' => 'API jalan']);
});


// ================= PUBLIC =================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/create-admin',[AuthController::class, 'createAdmin']);
Route::get('/nasabah/qrcode/{kode}',[UserController::class, 'scanQr']);


//data bank sampah
Route::get('/bank-sampahs',[BankSampahController::class, 'index']);


//transaksi buat nasabah
Route::post('/transaksi', [TransaksiController::class, 'store']);

//setor sampah buat nasabah
Route::post('/setor-sampah',[SetorSampahController::class,'store']);
Route::get('/setor-sampah', [SetorSampahController::class, 'index']);

//kurir
    // Route::get('/dashboard-kurir', [KurirController::class, 'dashboard_kurir']);
    Route::get('/kurir/jadwal/{id}', [JadwalPenjemputanController::class, 'jadwalKurir']);
    Route::get('/dashboard-kurir/{id}',[KurirController::class,'dashboard_kurir']
);

//jenis sampah buat web admin
Route::resource('/admin/jenis-sampah',JenisSampahWebController::class);

//jenis sampah buat bank sampah
Route::apiResource('jenis-sampah',JenisSampahController::class);
// Route::get('/jenis-sampahs', [JenisSampahController::class, 'index']);
// Route::post('/jenis-sampahs', [JenisSampahController::class, 'store']);
// Route::get('/jenis-sampahs/{id}', [JenisSampahController::class, 'show']);
Route::put('/jenis-sampahs/{id}', [JenisSampahController::class, 'update']);
Route::delete('/jenis-sampahs/{id}', [JenisSampahController::class, 'destroy']);

// ================= PROTECTED =================
Route::middleware('auth:sanctum')->group(function () {


// ================= NASABAH Transaksi=================
    
    // logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // ================= ADMIN DLH =================
    Route::middleware('admin_dlh')->group(function () {

        Route::prefix('bank-sampah')->group(function () {
            Route::post('/', [BankSampahController::class, 'store']);      // tambah bank sampah
            Route::get('/', [BankSampahController::class, 'index']);       // list bank sampah
            Route::get('/{id}', [BankSampahController::class, 'show']);    // detail
            Route::post('/{id}/approve', [BankSampahController::class, 'approve']); // approve
            Route::delete('/{id}', [BankSampahController::class, 'destroy']); // hapus
        });

    });


    // ================= ADMIN BANK SAMPAH =================
    Route::middleware('admin_bank')->group(function () {

        Route::post('/nasabah/{id}/approve', [UserController::class, 'approveNasabah']);
        Route::post('/kurir', [UserController::class, 'createKurir']);
       
        //jadwal penjemputan
        Route::post('/jadwal-penjemputan', [JadwalPenjemputanController::class, 'store']);


    });

    //jadwal penjemputan
    Route::post('/jadwal-penjemputan', [JadwalPenjemputanController::class, 'store']);

    //barcode
    Route::get('/barcode/nasabah/{id}',[BarcodeController::class,'barcodeNasabah']);

});