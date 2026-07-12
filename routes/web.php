<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\KurirWebController;
use App\Http\Controllers\JenisSampahWebController;
use App\Http\Controllers\JadwalWebController;
use App\Http\Controllers\NasabahWebController;
use App\Http\Controllers\SetorSampahWebController;
use App\Http\Controllers\TarikTunaiWebController;
use App\Http\Controllers\MasterJadwalRutinController;
use App\Http\Controllers\DlhDashboardController;
use App\Http\Controllers\DlhBankSampahWebController;
use App\Http\Controllers\DlhAduanWebController;


// Rute untuk menerima setup PIN dari Flutter
Route::post('/setup-pin', function (\Illuminate\Http\Request $request) {
    // Ini contoh logika sederhana untuk tes koneksi dari Flutter
    return response()->json([
        'status' => 'success',
        'message' => 'Rute setup-pin berhasil dihubungkan ke Laravel!'
    ], 200);
});


Route::get('/bersihkan-ingatan-rute', function () {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');

    return "Ingatan rute lama dihapus! Sekarang silakan buka: pht.my.id/up-db";
});

// Tambahkan rute ini untuk menjalankan migrasi database via browser
Route::get('/up-db', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return "✅ Database Berhasil Dimigrasi! Silakan cek tabel tarik_tunais.";
});

Route::get('/', function () {
    return redirect()->route('login');
});

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

Route::get('/buat-akun-admin', function () {
    \App\Models\User::updateOrCreate(
        ['email' => 'adminbasayan@gmail.com'],
        [
            'name' => 'Admin Bank Sampah',
            'password' => bcrypt('password123'),
            'role' => 'admin_bank_sampah',
            'status' => 'aktif',
            'alamat' => 'Kantor Bank Sampah',
            'no_hp' => '081122334455',
            'bank_sampah_id' => 1
        ]
    );
    return '✅ Akun Admin Bank Sampah berhasil dibuat! Silakan buka /login dengan email: adminbasayan@gmail.com dan password: password123';
});

Route::get('/login', [AdminWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminWebController::class, 'login']);
Route::post('/logout', [AdminWebController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('admin.dashboard');
    Route::resource('kurir', KurirWebController::class);
    Route::resource('jenis-sampah', JenisSampahWebController::class);
    Route::resource('jadwal', JadwalWebController::class);
    Route::resource('master-jadwal', MasterJadwalRutinController::class)->names([
        'index'   => 'master-jadwal.index',
        'create'  => 'master-jadwal.create',
        'store'   => 'master-jadwal.store',
        'destroy' => 'master-jadwal.destroy',
    ]);

    Route::prefix('setor-sampah')->group(function () {
        Route::get('/', [SetorSampahWebController::class, 'index'])->name('admin.setor.index');
        Route::get('/manual/{id}', [SetorSampahWebController::class, 'formManual'])->name('admin.setor.form-manual');
        Route::post('/manual/{id}', [SetorSampahWebController::class, 'prosesManual'])->name('admin.setor.proses-manual');
        Route::get('/{id}', [SetorSampahWebController::class, 'show'])->name('admin.setor.show');
        Route::post('/{id}/status', [SetorSampahWebController::class, 'updateStatus'])->name('admin.setor.updateStatus');
        Route::delete('/{id}', [SetorSampahWebController::class, 'destroy'])->name('admin.setor.destroy');
    });

    Route::prefix('nasabah')->group(function () {
        Route::get('/', [NasabahWebController::class, 'index'])->name('admin.nasabah.index');
        Route::get('/{id}/print-qr', [NasabahWebController::class, 'printQr'])->name('admin.nasabah.print-qr');
        Route::get('/{id}', [NasabahWebController::class, 'show'])->name('admin.nasabah.show');
        Route::post('/{id}/approve', [NasabahWebController::class, 'approve'])->name('admin.nasabah.approve');
        Route::post('/{id}/status', [NasabahWebController::class, 'updateStatus'])->name('admin.nasabah.updateStatus');
        Route::delete('/{id}', [NasabahWebController::class, 'destroy'])->name('admin.nasabah.destroy');
    });

    Route::prefix('tarik-tunai')->group(function () {
        Route::get('/', [TarikTunaiWebController::class, 'index'])->name('admin.tarik-tunai.index');
        Route::post('/{id}/approve', [TarikTunaiWebController::class, 'approve'])->name('admin.tarik-tunai.approve');
        Route::post('/{id}/reject', [TarikTunaiWebController::class, 'reject'])->name('admin.tarik-tunai.reject');
    });
    Route::get('/riwayat-penarikan', [TarikTunaiWebController::class, 'riwayat'])->name('admin.tarik-tunai.riwayat');

});

Route::prefix('dlh')->middleware(['auth', 'admin_dlh'])->group(function () {
    Route::get('/dashboard', [DlhDashboardController::class, 'index'])->name('dlh.dashboard');
    Route::prefix('bank-sampah')->group(function () {
        Route::get('/', [DlhBankSampahWebController::class, 'index'])
            ->name('dlh.bank-sampah.index');

        Route::get('/create', [DlhBankSampahWebController::class, 'create'])
            ->name('dlh.bank-sampah.create');

        Route::post('/', [DlhBankSampahWebController::class, 'store'])
            ->name('dlh.bank-sampah.store');

        Route::get('/{id}', [DlhBankSampahWebController::class, 'show'])
            ->name('dlh.bank-sampah.show');

        Route::get('/{id}/edit', [DlhBankSampahWebController::class, 'edit'])
            ->name('dlh.bank-sampah.edit');

        Route::put('/{id}', [DlhBankSampahWebController::class, 'update'])
            ->name('dlh.bank-sampah.update');

        Route::delete('/{id}', [DlhBankSampahWebController::class, 'destroy'])
            ->name('dlh.bank-sampah.destroy');

        Route::post('/{id}/approve', [DlhBankSampahWebController::class, 'approve'])
            ->name('dlh.bank-sampah.approve');
    });

    Route::prefix('aduan')->group(function () {
        Route::get('/', [DlhAduanWebController::class, 'index'])->name('dlh.aduan.index');
        Route::get('/{id}', [DlhAduanWebController::class, 'show'])->name('dlh.aduan.show');
        Route::put('/{id}', [DlhAduanWebController::class, 'update'])->name('dlh.aduan.update');
    });

});
