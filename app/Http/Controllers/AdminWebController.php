<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\JadwalPenjemputan; 
use App\Models\SetorSampah;       
use Carbon\Carbon;
use App\Models\BankSampah;
use App\Models\PengirimanMitra;
use Illuminate\Support\Facades\DB;

class AdminWebController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {

            // Kalau akun sudah nonaktif
            if (Auth::user()->status == 'nonaktif') {

                Auth::logout();

                return view('admin.login')
                    ->with('error','Akun Anda telah dinonaktifkan.');
            }

            if (Auth::user()->role == 'admin_dlh') {
                return redirect()->route('dlh.dashboard');
            }

            if (Auth::user()->role == 'admin_bank') {
                return redirect()->route('admin.dashboard');
            }

            Auth::logout();
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {

        $request->session()->regenerate();

        $user = Auth::user();

        // Cek status dulu
        if ($user->status == 'nonaktif') {

            Auth::logout();

            return back()->with(
                'error',
                'Akun Anda telah dinonaktifkan.'
            );
        }

        // Baru cek role
        if ($user->role == 'admin_dlh') {
            return redirect()->route('dlh.dashboard');
        }

        if ($user->role == 'admin_bank') {
            return redirect()->route('admin.dashboard');
        }

        // Selain role di atas
        Auth::logout();

        return back()->with(
            'error',
            'Akun ini khusus untuk pengguna aplikasi mobile.'
        );
    }

    return back()->with('error', 'Email atau password salah');
    }

    // ========================================================
    // METHOD DASHBOARD: DIUBAH AGAR MENGHITUNG DATA DINAMIS
    // ========================================================
    public function dashboard()
    {
        $adminBankId = Auth::user()->bank_sampah_id;

        // 1. Hitung total nasabah aktif
        $totalNasabah = User::where('role', 'nasabah')
            ->where('bank_sampah_id', $adminBankId)->count();

        // 2. Hitung total kurir aktif
        $totalKurir = User::where('role', 'kurir')
            ->where('bank_sampah_id', $adminBankId)->count();

        // 3. Hitung jumlah antrean rute penjemputan harian yang belum selesai (terjadwal atau proses)
        // Jika nama model harianmu adalah 'Jadwal', gantry bagian ini menjadi Jadwal::...
        $jadwalPending = JadwalPenjemputan::whereDate('tanggal_penjemputan', Carbon::today())
            ->where('bank_sampah_id', $adminBankId)
            ->whereIn('status', ['terjadwal', 'proses'])
            ->count();

        // 4. Hitung akumulasi berat (Kg) sampah yang berhasil disetor khusus HARI INI
        $beratHariIni = SetorSampah::whereHas('nasabah', function ($query) use ($adminBankId) {
                $query->where('bank_sampah_id', $adminBankId);
            })->whereDate('created_at', Carbon::today())->sum('berat');


        //Total Sampah
        $totalSampah = SetorSampah::where('status', 'selesai')->sum('berat');

        // Total Partner
        $totalPartner = User::where('role', 'mitra')->count();

        $tahunIni = 2026;
        // Jadwal Hari Ini
        $jadwalHariIni = JadwalPenjemputan::whereDate(
            'tanggal_penjemputan', Carbon::today()
        )->where('bank_sampah_id', $adminBankId)
        ->count();

        // Pengiriman Menunggu
        $pengirimanMenunggu = PengirimanMitra::where(
            'status_pengiriman',
            'Menunggu Mitra'
        )->count();

        // Pembayaran Menunggu Verifikasi
        $verifikasiPembayaran = PengirimanMitra::where(
            'status_pembayaran',
            'Menunggu Verifikasi'
        )->count();

        // Pembayaran Lunas
        $pembayaranLunas = PengirimanMitra::where(
            'status_pembayaran',
            'Lunas'
        )->count();

        $setoranPerBulan = SetorSampah::select(
            DB::raw('MONTH(created_at) as bulan'),
            DB::raw('SUM(berat) as total_berat')
        )
        ->where('status', 'selesai')
        ->whereYear('created_at', $tahunIni)
        ->groupBy(DB::raw('MONTH(created_at)'))
        ->pluck('total_berat', 'bulan') // Hasilnya: [5 => 9.8] (Bulan Mei saja yang terisi dari gambar)
        ->toArray();

        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $bulanData = [];
        $jumlahSetoranData = [];

        for ($i = 1; $i <= 12; $i++) {
            $bulanData[] = $namaBulan[$i - 1];
            // Jika bulan tersebut tidak ada datanya di DB, set otomatis ke 0
            $jumlahSetoranData[] = isset($setoranPerBulan[$i]) ? (float)$setoranPerBulan[$i] : 0;
        }

        // Balikkan ke view dashboard dengan membawa data ringkasan di atas
        return view('admin.dashboard', compact(
            'totalNasabah',
            'totalKurir',
            'jadwalPending',
            'beratHariIni',
            'totalSampah',
            'totalPartner',
            'jadwalHariIni',
            'pengirimanMenunggu',
            'verifikasiPembayaran',
            'pembayaranLunas',
            'bulanData',        
            'jumlahSetoranData'
        ));
    }

        public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // UBAH BAGIAN INI: gunakan route() bukan URL string
        return redirect()->route('login'); 
    }
}