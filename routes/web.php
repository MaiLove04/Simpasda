<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\KurirWebController;
use App\Http\Controllers\JenisSampahWebController;
use App\Http\Controllers\JadwalWebController;
use App\Http\Controllers\NasabahWebController;


Route::get('/', function () {

    return redirect(
        '/admin/login'
    );

});



Route::prefix('admin')->group(function () {


    // login
    Route::get(
        '/login',
        [AdminWebController::class, 'showLogin']
    );

    Route::post(
        '/login',
        [AdminWebController::class, 'login']
    );



    // protected
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

        //nasabah
        Route::get(
            '/nasabah',
            [NasabahWebController::class, 'index']
        );


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

        //status nasabah
        Route::post(
            '/nasabah/{id}/status',
            [NasabahWebController::class, 'updateStatus']
        );

    });

});