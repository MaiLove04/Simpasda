<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankSampah;
use App\Models\User;
use App\Models\Aduan;
// Tambahkan model transaksi/setoran sampah Anda di sini jika ada untuk data komoditas, 
// misalnya: use App\Models\Setoran; 

class DlhDashboardController extends Controller
{
    public function index()
    {
        // 1. Data Ringkasan Statistik
        $totalBankSampah = BankSampah::count();
        $totalNasabah = User::where('role', 'nasabah')->count();
        
        // Menyesuaikan status aduan sesuai query Anda ('menunggu')
        $aduanBaru = Aduan::where('status', 'menunggu')->count();

        // 2. Data Tabel Validasi Bank Sampah Pending
        $bankSampahPending = BankSampah::where('status', 'pending')->latest()->take(5)->get();

        // 3. Tambahan: Ambil Daftar Tiket Aduan Terbaru untuk ditampilkan di Blade
        $daftarAduanTerbaru = Aduan::whereIn('status', ['menunggu', 'proses'])
                                    ->latest()
                                    ->take(3)
                                    ->get();

        // 4. Tambahan: Simulasi Kalkulasi Komoditas Wilayah Real-Time
        // Catatan: Jika Anda sudah memiliki tabel setoran/transaksi sampah, silakan ganti logic di bawah ini
        // dengan query asli, misalnya: BankSampah::sum('sampah_organik'), dll.
        
        // Untuk mencegah error sebelum tabelnya siap, kita pasang data default dinamis:
        $persenOrganik   = 45;
        $persenAnorganik = 35;
        $persenKertas    = 20;

        // 5. Satukan semua variabel ke dalam compact()
        return view('dlh.dashboard', compact(
            'totalBankSampah', 
            'totalNasabah', 
            'aduanBaru', 
            'bankSampahPending',
            'daftarAduanTerbaru', // <-- Variabel baru
            'persenOrganik',      // <-- Variabel baru
            'persenAnorganik',    // <-- Variabel baru
            'persenKertas'        // <-- Variabel baru
        ));
    }
}