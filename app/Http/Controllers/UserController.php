<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaksi;
use App\Models\MutasiSaldo;
use Illuminate\Support\Facades\Hash;
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
            $nasabah = User::find($user_id);

            if (!$nasabah) {
                return response()->json(['success' => false, 'message' => 'Nasabah tidak ditemukan.'], 404);
            }

            $setorIds = DB::table('setor_sampahs')->where('user_id', $user_id)->pluck('id');
            $totalBeratSampah = 0;
            if (!empty($setorIds) && count($setorIds) > 0) {
                $totalBeratSampah = DB::table('detail_setor_sampahs')
                    ->whereIn('setor_sampah_id', $setorIds)
                    ->whereNotNull('berat')
                    ->sum('berat') ?? 0;
            }

            $mutasi = DB::table('mutasi_saldos')
                ->where('user_id', $user_id)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function($item) {
                    Carbon::setLocale('id');
                    $item->tanggal_formatted = Carbon::parse($item->created_at)->translatedFormat('d F Y, H:i') . ' WIB';
                    $isMasuk = strtolower($item->jenis_transaksi) === 'masuk';
                    $item->judul_dinamis = $isMasuk ? 'Setor Sampah Nasabah' : 'Tarik Tunai Dana';
                    $item->total_berat = $isMasuk ? 'Lihat Detail' : '0';
                    return $item;
                });

            // 🔥 Hitung saldo pending (hanya untuk tampilan indikator di HP saja, tidak memotong saldo utama)
            $saldoPending = DB::table('mutasi_saldos')
                ->where('user_id', $user_id)
                ->where('sumber', 'tarik_tunai')
                ->where('status', 'pending')
                ->sum('nominal') ?? 0;

            return response()->json([
                'success' => true,
                'message' => 'Data dashboard nasabah berhasil dimuat.',
                'nasabah' => [
                    'id' => $nasabah->id,
                    'name' => $nasabah->name,
                    'email' => $nasabah->email,
                    'alamat' => $nasabah->alamat,
                    'saldo_aktif' => (int) ($nasabah->saldo ?? 0), // Saldo utuh tidak berkurang saat pending
                    'saldo_pending' => (int) $saldoPending,       // Menampilkan jumlah yang sedang diajukan
                    'saldo' => (int) ($nasabah->saldo ?? 0),
                    'total_berat_kg' => round((double)$totalBeratSampah, 1),
                    'has_pin' => !empty($nasabah->pin_hash),
                ],
                'riwayat_mutasi' => $mutasi
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'message' => 'Safe Mode Aktif: ' . $e->getMessage(),
                'nasabah' => [
                    'id' => (int)$user_id,
                    'name' => 'Nasabah',
                    'saldo_aktif' => 0,
                    'saldo_pending' => 0,
                    'saldo' => 0,
                    'total_berat_kg' => 0.0,
                ],
                'riwayat_mutasi' => []
            ], 200);
        }
    }

    /**
     * Set PIN nasabah pertama kali
     */
    public function setupPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:6|confirmed',
        ]);

        $user = $request->user();

        if ($user->pin_hash != null) {
            return response()->json(['success' => false, 'message' => 'PIN sudah terpasang.'], 400);
        }

        $user->update([
            'pin_hash' => Hash::make($request->pin),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN transaksi berhasil dibuat.'
        ]);
    }

    /**
     * Helper Verifikasi PIN (Internal)
     */
    private function verifyTransactionPin($user, $pin)
    {
        // 1. Cek apakah sedang dikunci
        if ($user->pin_locked_until && now()->lessThan($user->pin_locked_until)) {
            $diff = now()->diffInMinutes($user->pin_locked_until);
            throw new \Exception("PIN terblokir sementara. Coba lagi dalam $diff menit.");
        }

        // 2. Cek kecocokan hash
        if (!Hash::check($pin, $user->pin_hash)) {
            $user->increment('pin_attempts');

            if ($user->pin_attempts >= 3) {
                $user->update([
                    'pin_locked_until' => now()->addMinutes(30),
                    'pin_attempts' => 0
                ]);
                throw new \Exception("PIN salah 3x. Akun diblokir sementara 30 menit.");
            }

            throw new \Exception("PIN yang Anda masukkan salah.");
        }

        // 3. Reset hitungan jika benar
        $user->update(['pin_attempts' => 0, 'pin_locked_until' => null]);
        return true;
    }

    /**
     * PERBAIKAN TOTAL: Pengajuan penarikan via aplikasi berstatus pending
     */
    public function tarikTunai(Request $request)
    {
        $request->validate([
        'user_id'  => 'required',
        'nominal'  => [
            'required',
            'integer',
            'min:5000',
            function ($attribute, $value, $fail) {
                if ($value % 500 !== 0) {
                    $fail('Nominal penarikan harus kelipatan Rp 500 (Contoh: 5.000, 5.500).');
                }
            },
        ],
        'metode'   => 'required',
        'nomor_hp' => 'required'
        ,
    ]);

    $user = $request->user();
        if ($user->id != $request->user_id) {
            return response()->json(['success' => false, 'message' => 'ID Nasabah tidak valid.'], 403);
        }

        try {
            $this->verifyTransactionPin($user, $request->pin);

            // Cek apakah saldo mencukupi
            if ($user->saldo < $request->nominal) {
                return response()->json(['success' => false, 'message' => 'Saldo tidak mencukupi.'], 400);
            }

            DB::beginTransaction();

            $externalId = 'WD-' . time() . '-' . $user->id;

            // 🔥 SALDO TIDAK DIPOTONG DI SINI (Dibiarkan utuh)

            // Catat ke Mutasi Saldo dengan STATUS PENDING
            $mutasi = new MutasiSaldo();
            $mutasi->user_id = $user->id;
            $mutasi->jenis_transaksi = 'keluar';
            $mutasi->sumber = 'tarik_tunai';
            $mutasi->nominal = $request->nominal;
            $mutasi->status = 'pending'; 
            $mutasi->keterangan = "Penarikan via {$request->metode} ke {$request->nomor_hp} (Menunggu Persetujuan)";
            $mutasi->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan penarikan berhasil dikirim. Menunggu persetujuan admin.',
                'transaction_id' => $externalId,
                'saldo_terakhir' => $user->saldo // Saldo yang dikirim masih saldo utuh
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }
}