<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    /**
     * Approve user oleh admin DLH
     */
    public function approve(Request $request)
    {
        // validasi input
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // ambil user
        $user = User::find($request->user_id);

        // update status
        $user->status = 'approved';
        $user->save();

        return response()->json([
            'message' => 'User berhasil di-approve',
            'data' => $user
        ]);
    }

    /**
     * List semua user (optional, buat admin)
     */
    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }

    public function scanQr($kode)
    {
        $nasabah = User::where(
            'kode_nasabah',
            $kode
        )
        ->where('role', 'nasabah')
        ->first();

        if (!$nasabah) {

            return response()->json([
                'message' => 'Nasabah tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'id' => $nasabah->id,
            'name' => $nasabah->name,
            'alamat' => $nasabah->alamat,
            'kode_nasabah' => $nasabah->kode_nasabah,
        ]);
    }
    
    public function dashboard_nasabah($user_id)
    {
        try {
            // 1. Ambil data profil dasar nasabah & sisa saldo ter-update
            $nasabah = \App\Models\User::select('id', 'name', 'email', 'alamat', 'saldo')
                                       ->find($user_id);

            if (!$nasabah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nasabah tidak ditemukan.'
                ], 404);
            }

            // 🔥 FIX UTAMA: Ambil semua ID transaksi khusus BULAN INI SAJA
            $setorBulanIniIds = \App\Models\SetorSampah::where('user_id', $user_id)
                ->whereMonth('created_at', \Carbon\Carbon::now()->month)
                ->whereYear('created_at', \Carbon\Carbon::now()->year)
                ->pluck('id');

            // Hitung akumulasi TOTAL BERAT (Kg) dari detail timbangan anak khusus bulan ini
            $totalBeratSampahBulanIni = \App\Models\DetailSetorSampah::whereIn('setor_sampah_id', $setorBulanIniIds)
                ->sum('berat');

            // 2. Ambil riwayat mutasi keuangan saldo (Uang Masuk / Keluar)
            $mutasi = \App\Models\MutasiSaldo::where('user_id', $user_id)
                        ->latest()
                        ->get()
                        ->map(function($item) {
                            \Carbon\Carbon::setLocale('id');
                            $item->tanggal_formatted = \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y, H:i') . ' WIB';
                            return $item;
                        });

            // 3. Return gabungan paket JSON ke Flutter Nasabah
            return response()->json([
                'success' => true,
                'message' => 'Data dashboard nasabah berhasil dimuat.',
                'nasabah' => [
                    'id' => $nasabah->id,
                    'name' => $nasabah->name,
                    'email' => $nasabah->email,
                    'alamat' => $nasabah->alamat,
                    'saldo' => (int) $nasabah->saldo, 
                    'total_berat_kg' => round($totalBeratSampahBulanIni, 1), // 🍏 Sekarang murni total berat 1 bulan berjalan!
                ],
                'riwayat_mutasi' => $mutasi
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
}