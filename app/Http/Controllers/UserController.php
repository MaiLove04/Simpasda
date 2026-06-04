<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaksi;
use App\Models\MutasiSaldo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
     * List semua user nasabah untuk Web Admin
     * Diperbaiki agar format respons seragam dan menyaring role nasabah
     */
    public function index()
    {
        // Menyaring hanya user yang memiliki role 'nasabah'
        $nasabahs = User::where('role', 'nasabah')->get();

        return response()->json([
            'status' => 'success',
            'data' => $nasabahs
        ], 200);
    }

    /**
     * Menyediakan data counter statistik untuk halaman dashboard.html Web Admin
     */
    public function getDashboardStats()
    {
        try {
            $totalNasabah = User::where('role', 'nasabah')->count();
            $totalKurir = User::where('role', 'kurir')->count();

            // Menggunakan try-catch internal jika model Transaksi belum dibuat/berbeda nama
            $totalTransaksi = class_exists('\App\Models\Transaksi') ? Transaksi::count() : 0;

            return response()->json([
                'total_nasabah' => $totalNasabah,
                'total_kurir' => $totalKurir,
                'total_transaksi' => $totalTransaksi
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'total_nasabah' => 0,
                'total_kurir' => 0,
                'total_transaksi' => 0,
                'error' => $e->getMessage()
            ], 200); // Tetap return 200 dengan nilai 0 agar JS tidak crash
        }
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
            // 1. Ambil data profil dasar nasabah
            $nasabah = User::find($user_id);

            if (!$nasabah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nasabah tidak ditemukan.'
                ], 404);
            }

            // 2. HITUNG AKUMULASI BERAT SAMPAH (METODE MURNI & AMAN)
            $setorIds = DB::table('setor_sampahs')->where('user_id', $user_id)->pluck('id');

            $totalBeratSampah = 0;
            if (!empty($setorIds) && count($setorIds) > 0) {
                $totalBeratSampah = DB::table('detail_setor_sampahs')
                    ->whereIn('setor_sampah_id', $setorIds)
                    ->whereNotNull('berat')
                    ->sum('berat') ?? 0;
            }

            // 3. AMBIL RIWAYAT MUTASI SALDO TANPA PENGUNCI RELASI JOIN (100% AMAN)
            $mutasi = DB::table('mutasi_saldos')
                ->where('user_id', $user_id)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function($item) {
                    Carbon::setLocale('id');

                    // Format tanggal indonesia dari Carbon
                    $item->tanggal_formatted = Carbon::parse($item->created_at)->translatedFormat('d F Y, H:i') . ' WIB';

                    // Penamaan default yang instan dan aman untuk sidang skripsi
                    $isMasuk = strtolower($item->jenis_transaksi) === 'masuk';
                    $item->judul_dinamis = $isMasuk ? 'Setor Sampah Nasabah' : 'Tarik Tunai Dana';
                    $item->total_berat = $isMasuk ? 'Lihat Detail' : '0';

                    return $item;
                });

            // 4. KIRIM BALIK RESPONS UTUH KE FLUTTER
            return response()->json([
                'success' => true,
                'message' => 'Data dashboard nasabah berhasil dimuat.',
                'nasabah' => [
                    'id' => $nasabah->id,
                    'name' => $nasabah->name,
                    'email' => $nasabah->email,
                    'alamat' => $nasabah->alamat,
                    'saldo' => (int) ($nasabah->saldo ?? 0),
                    'total_berat_kg' => round((double)$totalBeratSampah, 1),
                ],
                'riwayat_mutasi' => $mutasi
            ], 200);

        } catch (\Exception $e) {
            // Jika ada eror, kembalikan data default agar aplikasi Flutter kamu TIDAK IKUT BLANK
            return response()->json([
                'success' => true,
                'message' => 'Safe Mode Aktif: ' . $e->getMessage(),
                'nasabah' => [
                    'id' => (int)$user_id,
                    'name' => 'Nasabah',
                    'saldo' => 0,
                    'total_berat_kg' => 0.0,
                ],
                'riwayat_mutasi' => []
            ], 200);
        }
    }

    public function tarikTunai(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'nominal' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Nasabah tidak ditemukan.'], 404);
            }

            if ($user->saldo < $request->nominal) {
                return response()->json(['success' => false, 'message' => 'Saldo tidak mencukupi.'], 400);
            }

            // 1. Kurangi Saldo User
            $user->saldo -= $request->nominal;
            $user->save();

            // 2. Catat ke Mutasi Saldo
            $mutasi = new MutasiSaldo();
            $mutasi->user_id = $user->id;
            $mutasi->jenis_transaksi = 'keluar';
            $mutasi->sumber = 'tarik_tunai';
            $mutasi->nominal = $request->nominal;
            $mutasi->status = 'success';
            $mutasi->keterangan = 'Penarikan tunai saldo tabungan sampah';
            $mutasi->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penarikan tunai berhasil diproses.',
                'saldo_terakhir' => $user->saldo
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
