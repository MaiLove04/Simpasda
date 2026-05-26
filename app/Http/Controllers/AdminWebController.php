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
            return redirect('/admin/dashboard');
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

            // Pengecekan role sesuai bawaan sistemmu
            if ($user->role !== 'admin_bank') {
                Auth::logout();
                return back()->with('error', 'Bukan admin bank sampah');
            }

            $request->session()->regenerate();

            return redirect('/admin/dashboard');
        }

        return back()->with('error', 'Email atau password salah');
    }

    // ========================================================
    // METHOD DASHBOARD: DIUBAH AGAR MENGHITUNG DATA DINAMIS
    // ========================================================
    public function dashboard()
    {
        // 1. Hitung total nasabah aktif
        $totalNasabah = User::where('role', 'nasabah')->count();

        // 2. Hitung total kurir aktif
        $totalKurir = User::where('role', 'kurir')->count();

        // 3. Hitung jumlah antrean rute penjemputan harian yang belum selesai (terjadwal atau proses)
        // Jika nama model harianmu adalah 'Jadwal', gantry bagian ini menjadi Jadwal::...
        $jadwalPending = JadwalPenjemputan::whereDate('tanggal_penjemputan', Carbon::today())
            ->whereIn('status', ['terjadwal', 'proses'])
            ->count();

        // 4. Hitung akumulasi berat (Kg) sampah yang berhasil disetor khusus HARI INI
        $beratHariIni = SetorSampah::whereDate('created_at', Carbon::today())->sum('berat');

        // Balikkan ke view dashboard dengan membawa data ringkasan di atas
        return view('admin.dashboard', compact('totalNasabah', 'totalKurir', 'jadwalPending', 'beratHariIni'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}