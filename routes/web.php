<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\KurirWebController;
use App\Http\Controllers\JenisSampahWebController;
use App\Http\Controllers\JadwalWebController;
use App\Http\Controllers\NasabahWebController;
use App\Http\Controllers\SetorSampahWebController; 
use App\Http\Controllers\MasterJadwalRutinController;
use App\Http\Controllers\DlhDashboardController;
use App\Http\Controllers\DlhBankSampahWebController;
use App\Http\Controllers\DlhAduanWebController;

Route::get('/', function () {
    return redirect('/login');
});

// 🔥 JALANKAN URL INI DI BROWSER UNTUK MEMBUAT AKUN DLH OTOMATIS
Route::get('/buat-akun-dlh', function () {
    \App\Models\User::updateOrCreate(
        ['email' => 'dlh@gmail.com'], // Cek jika email ini sudah ada, maka update
        [
            'name' => 'Admin DLH Pusat',
            'password' => bcrypt('password123'),
            'role' => 'admin_dlh',
            'status' => 'aktif',
            'alamat' => 'Kantor DLH Pusat',
            'no_hp' => '081234567899',
            'bank_sampah_id' => 1 // Kosongkan atau isi 1 tergantung database
        ]
    );
    return '✅ Akun DLH berhasil dibuat! Silakan buka /login dengan email: dlh@gmail.com dan password: password123';
});

// ========================================================
// ROUTE LOGIN & LOGOUT UNIVERSAL (WEB)
// ========================================================
Route::get('/login', [AdminWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminWebController::class, 'login']);
Route::post('/logout', [AdminWebController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('admin')->group(function () {

    // Protected (Hanya bisa diakses jika sudah login admin)
    Route::middleware('auth')->group(function () {

        Route::get(
            '/dashboard',
            [AdminWebController::class, 'dashboard']
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

// ========================================================
// ROUTE KHUSUS ADMIN DINAS LINGKUNGAN HIDUP (DLH)
// ========================================================
Route::prefix('dlh')->middleware(['auth', 'admin_dlh'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DlhDashboardController::class, 'index'])->name('dlh.dashboard');

    // Kelola Bank Sampah
    Route::get('/bank-sampah', [DlhBankSampahWebController::class, 'index'])->name('dlh.bank-sampah.index');
    Route::post('/bank-sampah/{id}/approve', [DlhBankSampahWebController::class, 'approve'])->name('dlh.bank-sampah.approve');
    Route::delete('/bank-sampah/{id}', [DlhBankSampahWebController::class, 'destroy'])->name('dlh.bank-sampah.destroy');

    // Kelola Ticketing/Aduan
    Route::get('/aduan', [DlhAduanWebController::class, 'index'])->name('dlh.aduan.index');
    Route::get('/aduan/{id}', [DlhAduanWebController::class, 'show'])->name('dlh.aduan.show');
    Route::put('/aduan/{id}', [DlhAduanWebController::class, 'update'])->name('dlh.aduan.update');
    
});