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

// Otomatis mengarahkan rute utama '/' langsung ke halaman Login
Route::get('/', function () {
    return redirect()->route('login');
});

// 🔥 JALANKAN URL INI DI BROWSER UNTUK MEMBUAT AKUN DLH OTOMATIS
Route::get('/buat-akun-dlh', function () {
    \App\Models\User::updateOrCreate(
        ['email' => 'dlh@gmail.com'], 
        [
            'name' => 'Admin DLH Pusat',
            'password' => bcrypt('password123'),
            'role' => 'admin_dlh',
            'status' => 'aktif',
            'alamat' => 'Kantor DLH Pusat',
            'no_hp' => '081234567899',
            'bank_sampah_id' => 1 
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


// ========================================================
// ROUTE KHUSUS ADMIN BANK SAMPAH (OPERASIONAL LOKAL)
// ========================================================
Route::prefix('admin')->middleware('auth')->group(function () {

    // Dashboard Internal Bank Sampah
    Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('admin.dashboard');

    // Master Data Manajemen Operasional (Otomatis dapat named routes dari Laravel)
    Route::resource('kurir', KurirWebController::class);
    Route::resource('jenis-sampah', JenisSampahWebController::class);
    Route::resource('jadwal', JadwalWebController::class);

    // Master Jadwal Rutin Penjemputan
    Route::resource('master-jadwal', MasterJadwalRutinController::class)->names([
        'index'   => 'master-jadwal.index',
        'create'  => 'master-jadwal.create',
        'store'   => 'master-jadwal.store',
        'destroy' => 'master-jadwal.destroy',
    ]);

    // ========================================================
    // MODUL TRANSAKSI SETOR SAMPAH (Sudah Diberi Nama Rute Konsisten)
    // ========================================================
    Route::prefix('setor-sampah')->group(function () {
        Route::get('/', [SetorSampahWebController::class, 'index'])->name('admin.setor.index');
        Route::get('/manual/{id}', [SetorSampahWebController::class, 'formManual'])->name('admin.setor.form-manual');
        Route::post('/manual/{id}', [SetorSampahWebController::class, 'prosesManual'])->name('admin.setor.proses-manual');
        Route::get('/{id}', [SetorSampahWebController::class, 'show'])->name('admin.setor.show');
        Route::post('/{id}/status', [SetorSampahWebController::class, 'updateStatus'])->name('admin.setor.updateStatus');
        Route::delete('/{id}', [SetorSampahWebController::class, 'destroy'])->name('admin.setor.destroy');
    });

    // ========================================================
    // MODUL DATA KEANGGOTAAN NASABAH (⚠️ PERBAIKAN: Kini Sudah Ada Named Routes)
    // ========================================================
    Route::prefix('nasabah')->group(function () {
        Route::get('/', [NasabahWebController::class, 'index'])->name('admin.nasabah.index');
        Route::get('/{id}/print-qr', [NasabahWebController::class, 'printQr'])->name('admin.nasabah.print-qr');
        Route::get('/{id}', [NasabahWebController::class, 'show'])->name('admin.nasabah.show');
        Route::post('/{id}/approve', [NasabahWebController::class, 'approve'])->name('admin.nasabah.approve');
        Route::post('/{id}/status', [NasabahWebController::class, 'updateStatus'])->name('admin.nasabah.updateStatus');
        Route::delete('/{id}', [NasabahWebController::class, 'destroy'])->name('admin.nasabah.destroy');
    });

    // ========================================================
    // MODUL KEUANGAN / TARIK TUNAI NASABAH (Sudah Diberi Nama Rute Konsisten)
    // ========================================================
    Route::prefix('tarik-tunai')->group(function () {
        Route::get('/', [NasabahWebController::class, 'indexTarikTunai'])->name('admin.tarik-tunai.index');
        Route::get('/{id}', [NasabahWebController::class, 'tarikTunai'])->name('admin.tarik-tunai.form');
        Route::post('/{id}', [NasabahWebController::class, 'prosesTarikTunai'])->name('admin.tarik-tunai.proses');
    });
    Route::get('/riwayat-penarikan', [NasabahWebController::class, 'riwayatPenarikan'])->name('admin.tarik-tunai.riwayat');
        
});


// ========================================================
// ROUTE KHUSUS ADMIN DINAS LINGKUNGAN HIDUP (DLH)
// ========================================================
Route::prefix('dlh')->middleware(['auth', 'admin_dlh'])->group(function () {
    
    // Dashboard Utama Statistik DLH
    Route::get('/dashboard', [DlhDashboardController::class, 'index'])->name('dlh.dashboard');

    // Modul Pengesahan & Verifikasi Unit Bank Sampah Baru
    Route::prefix('bank-sampah')->group(function () {
        Route::get('/', [DlhBankSampahWebController::class, 'index'])->name('dlh.bank-sampah.index');
        Route::post('/{id}/approve', [DlhBankSampahWebController::class, 'approve'])->name('dlh.bank-sampah.approve');
        Route::delete('/{id}', [DlhBankSampahWebController::class, 'destroy'])->name('dlh.bank-sampah.destroy');
    });

    // Modul Pusat Pengaduan Kendala & Fasilitas Sampah Warga
    Route::prefix('aduan')->group(function () {
        Route::get('/', [DlhAduanWebController::class, 'index'])->name('dlh.aduan.index');
        Route::get('/{id}', [DlhAduanWebController::class, 'show'])->name('dlh.aduan.show');
        Route::put('/{id}', [DlhAduanWebController::class, 'update'])->name('dlh.aduan.update');
    });
    
});