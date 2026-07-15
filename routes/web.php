<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\KurirWebController;
use App\Http\Controllers\JenisSampahWebController;
use App\Http\Controllers\JadwalWebController;
use App\Http\Controllers\NasabahWebController;
use App\Http\Controllers\SetorSampahWebController; 
use App\Http\Controllers\MasterJadwalRutinController;
use App\Http\Controllers\TarikTunaiWebController;
use App\Http\Controllers\DlhDashboardController;
use App\Http\Controllers\DlhBankSampahWebController;
use App\Http\Controllers\DlhAduanWebController;
use App\Http\Controllers\OperasionalController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\PengurusController;
use App\Http\Controllers\PengirimanMitraController;
use App\Http\Controllers\Partner\AuthPartnerController;
use App\Http\Controllers\Partner\DashboardController;
use App\Http\Controllers\Partner\PengirimanController;
use App\Http\Controllers\Partner\ProfilController;
use App\Http\Controllers\Partner\PembayaranController;

/// Otomatis mengarahkan rute utama '/' langsung ke halaman Login
Route::get('/', function () {
     return view('landing');
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
    Route::resource('kurir', KurirWebController::class)->names("kurir-admin");
    Route::resource('jenis-sampah', JenisSampahWebController::class)->names("jenis-sampah-admin");
    Route::put('jenis-sampah/{id}/toggle-status', [JenisSampahWebController::class, 'toggleStatus'])->name('jenis-sampah.toggle-status');
    Route::resource('jadwal', JadwalWebController::class);
    
    // Rute Force Sync Manual penutupan jadwal rutin
    Route::post('/master-jadwal/generate', [MasterJadwalRutinController::class, 'generate'])->name('master-jadwal.generate');

    // CRUD Master Jadwal Rutin Nasabah
    Route::resource('master-jadwal', MasterJadwalRutinController::class)->names([
        'index'   => 'master-jadwal.index',
        'create'  => 'master-jadwal.create',
        'store'   => 'master-jadwal.store',
        'destroy' => 'master-jadwal.destroy',
    ]);

    //kelola kurir
    

    // ========================================================
    // MODUL TRANSAKSI SETOR SAMPAH (KINI MENDUKUNG LOKET MANUAL)
    // ========================================================
    Route::prefix('setor-sampah')->group(function () {
        $controller = SetorSampahWebController::class;
        Route::get('/', [$controller, 'index'])->name('admin.setor.index');
        
        // ➕ TAMBAHAN BARU: Rute Loket Masuk Umum tanpa ID (Untuk tombol di Index yang kita buat sebelumnya)
        Route::get('/manual', [$controller, 'createManual'])->name('admin.setor.manual');
        Route::post('/manual', [$controller, 'storeManual'])->name('admin.setor.store-manual');

        // Input manual spesifik lewat detail/QR profil nasabah tertentu
        Route::get('/manual/{id}', [$controller, 'formManual'])->name('admin.setor.form-manual');
        Route::post('/manual/{id}', [$controller, 'prosesManual'])->name('admin.setor.proses-manual');
        
        Route::get('/{id}', [$controller, 'show'])->name('admin.setor.show');
        Route::delete('/{id}', [$controller, 'destroy'])->name('admin.setor.destroy');
    });

    // ========================================================
    // MODUL DATA KEANGGOTAAN NASABAH
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
    // MODUL KEUANGAN / TARIK TUNAI NASABAH
    // ========================================================
    Route::prefix('tarik-tunai')->group(function () {
        $controller = TarikTunaiWebController::class;
        Route::get('/', [$controller, 'index'])->name('admin.tarik-tunai.index');
        Route::patch('/{id}/approve', [$controller, 'approve'])->name('admin.tarik-tunai.approve');
        Route::patch('/{id}/reject', [$controller, 'reject'])->name('admin.tarik-tunai.reject');
    });
    Route::get('/riwayat-penarikan', [TarikTunaiWebController::class, 'riwayat'])->name('admin.tarik-tunai.riwayat');
        
    // Fitur Operasional Pegawai & Penggajian
    Route::resource('Operasional', OperasionalController::class);

        Route::get(
            'Operasional/export/pdf',
            [OperasionalController::class,'exportPdf']
        )->name('Operasional.exportPdf');

        Route::get(
            'Operasional/import',
            [OperasionalController::class,'importForm']
        )->name('Operasional.importForm');

        Route::post(
            'Operasional/import',
            [OperasionalController::class,'importExcel']
        )->name('Operasional.importExcel');
        Route::prefix('kelola-admin')->group(function () {

        Route::get('/', [App\Http\Controllers\AdminManagementController::class, 'index'])
            ->name('kelola-admin.index');

        Route::get('/create', [App\Http\Controllers\AdminManagementController::class, 'create'])
            ->name('kelola-admin.create');

        Route::post('/store', [App\Http\Controllers\AdminManagementController::class, 'store'])
            ->name('kelola-admin.store');

        Route::get('/edit/{id}', [App\Http\Controllers\AdminManagementController::class, 'edit'])
            ->name('kelola-admin.edit');

        Route::put('/update/{id}', [App\Http\Controllers\AdminManagementController::class, 'update'])
            ->name('kelola-admin.update');

        Route::patch('/status/{id}', [App\Http\Controllers\AdminManagementController::class, 'toggleStatus'])
            ->name('kelola-admin.status');

        Route::get('/detail/{id}', [App\Http\Controllers\AdminManagementController::class, 'show'])
        ->name('kelola-admin.show');
        

    });

    // ========================================================
    // FITUR MITRA BANK SAMPAH
    // ========================================================
    Route::resource('Mitra', MitraController::class);
    Route::post('Mitra/{id}/buat-akun',[MitraController::class, 'buatAkun'])->name('Mitra.buat-akun');
    Route::post('Mitra/{id}/reset-password',[MitraController::class, 'resetPassword'])->name('Mitra.resetPassword');


    // ========================================================
    // PENGIRIMAN MITRA
    // ========================================================
    Route::resource('pengiriman-mitra', PengirimanMitraController::class);
    Route::post('pengiriman-mitra/{id}/terima',[PengirimanMitraController::class, 'terima'])->name('pengiriman-mitra.terima');
    Route::post('pengiriman-mitra/{id}/pembayaran',[PengirimanMitraController::class, 'pembayaran'])->name('pengiriman-mitra.pembayaran');
    Route::post('pengiriman-mitra/{id}/verifikasi',[PengirimanMitraController::class, 'verifikasi'])->name('pengiriman-mitra.verifikasi');
        
});


// ========================================================
// ROUTE KHUSUS ADMIN DINAS LINGKUNGAN HIDUP (DLH)
// ========================================================
Route::prefix('dlh')->middleware(['auth', 'admin_dlh'])->group(function () {
    
    Route::get('/dashboard', [DlhDashboardController::class, 'index'])->name('dlh.dashboard');

    // Modul Manajemen Bank Sampah oleh DLH
    Route::prefix('bank-sampah')->group(function () {
        Route::get('/', [DlhBankSampahWebController::class, 'index'])->name('dlh.bank-sampah.index');
        Route::get('/create', [DlhBankSampahWebController::class, 'create'])->name('dlh.bank-sampah.create');
        Route::post('/', [DlhBankSampahWebController::class, 'store'])->name('dlh.bank-sampah.store');
        Route::get('/{id}', [DlhBankSampahWebController::class, 'show'])->name('dlh.bank-sampah.show');
        Route::get('/{id}/edit', [DlhBankSampahWebController::class, 'edit'])->name('dlh.bank-sampah.edit');
        Route::put('/{id}', [DlhBankSampahWebController::class, 'update'])->name('dlh.bank-sampah.update');
        Route::delete('/{id}', [DlhBankSampahWebController::class, 'destroy'])->name('dlh.bank-sampah.destroy');
        Route::post('/{id}/approve', [DlhBankSampahWebController::class, 'approve'])->name('dlh.bank-sampah.approve');
    });

    // Modul Pusat Pengaduan Kendala & Fasilitas Sampah Warga
    Route::prefix('aduan')->group(function () {
        Route::get('/', [DlhAduanWebController::class, 'index'])->name('dlh.aduan.index');
        Route::get('/{id}', [DlhAduanWebController::class, 'show'])->name('dlh.aduan.show');
        Route::put('/{id}', [DlhAduanWebController::class, 'update'])->name('dlh.aduan.update');
    }); 
});

// ========================================================
// ROUTE PARTNER (LOGIN & DASHBOARD)
// ========================================================
Route::prefix('partner')->group(function () {
    Route::get('/login', [AuthPartnerController::class,'showLogin'])->name('partner.login');
    Route::post('/login', [AuthPartnerController::class,'login'])->name('partner.login.post');
});

Route::prefix('partner')->middleware(['auth', 'partner'])->group(function () {
    Route::get('/dashboard',[DashboardController::class, 'index'])->name('partner.dashboard');
    Route::resource('pengiriman', PengirimanController::class);
    Route::post('pengiriman/{id}/terima', [PengirimanController::class, 'terima'])->name('partner.pengiriman.terima');
    Route::post('pengiriman/{id}/pembayaran',[PengirimanController::class, 'pembayaran'])->name('partner.pengiriman.pembayaran');
    Route::resource('profil', ProfilController::class);
    Route::post('/logout',[AuthPartnerController::class, 'logout'])->name('partner.logout');
    Route::get('/pembayaran',[PembayaranController::class,'index'])->name('partner.pembayaran.index');
});