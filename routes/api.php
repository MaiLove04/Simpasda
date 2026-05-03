<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankSampahController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransaksiController;


// ================= TEST =================
Route::get('/test', function () {
    return response()->json(['message' => 'API jalan']);
});


// ================= PUBLIC =================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/bank-sampahs',[BankSampahController::class, 'index']);


//transaksi buat nasabah
    Route::post('/transaksi', [TransaksiController::class, 'store']);


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
        

    });

});