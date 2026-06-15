<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\JadwalPenjemputan; // Sesuai nama model jadwal harianmu, atau sesuaikan jika namanya 'Jadwal'
use App\Models\SetorSampah;       // Sesuai nama model transaksi setoranmu
use Carbon\Carbon;

class AdminWebController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin_dlh') {
                return redirect()->route('dlh.dashboard');
            } elseif (Auth::user()->role === 'admin_bank') {
                return redirect('/admin/dashboard');
            }
            
            // Kick user yang mencoba akses via URL login namun dia bukan admin
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
            $user = Auth::user();

            // Cek role untuk memisahkan pintu masuk admin_dlh dan admin_bank
            if ($user->role === 'admin_dlh') {
                $request->session()->regenerate();
                return redirect()->route('dlh.dashboard');
            } elseif ($user->role === 'admin_bank') {
                $request->session()->regenerate();
                return redirect('/admin/dashboard');
            } else {
                Auth::logout();
                return back()->with('error', 'Akun ini khusus untuk pengguna aplikasi mobile. Silakan gunakan aplikasi mobile untuk login.');
            }
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

        // Balikkan ke view dashboard dengan membawa data ringkasan di atas
        return view('admin.dashboard', compact('totalNasabah', 'totalKurir', 'jadwalPending', 'beratHariIni'));
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