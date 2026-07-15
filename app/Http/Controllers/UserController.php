<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaksi;
use App\Models\MutasiSaldo;
use App\Models\TarikTunai; // 🔥 DIPASTIKAN IMPORT MODEL INI ADA DI ATAS
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
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->user_id);
        $user->status = 'approved';
        $user->save();

        return response()->json([
            'message' => 'User berhasil di-approve',
            'data' => $user
        ]);
    }

    /**
     * List semua user nasabah untuk Web Admin
     */
    public function index()
    {
        $nasabahs = User::where('role', 'nasabah')->get();

        return response()->json([
            'status' => 'success',
            'data' => $nasabahs
        ], 200);
    }

    /**
     * Menyediakan data counter statistik untuk halaman dashboard Web Admin
     */
    public function getDashboardStats()
    {
        try {
            $totalNasabah = User::where('role', 'nasabah')->count();
            $totalKurir = User::where('role', 'kurir')->count();
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
            ], 200);
        }
    }

    /**
     * Menghasilkan QR Code untuk nasabah yang sedang login.
     * Endpoint ini dipanggil dari mobile app di rute /barcode/nasabah.
     */
    public function getQrCode(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || $user->role !== 'nasabah' || !$user->kode_nasabah) {
                return response()->json(['success' => false, 'message' => 'Data nasabah tidak valid untuk membuat QR Code.'], 404);
            }

            // Inisialisasi renderer untuk format PNG dengan ukuran 400px
            $renderer = new ImageRenderer(
                new RendererStyle(400),
                new Png()
            );

            // Inisialisasi writer dengan renderer yang sudah dibuat
            $writer = new Writer($renderer);

            // Generate data gambar QR code dari kode nasabah
            $qrCodeImage = $writer->writeString($user->kode_nasabah);

            // Encode gambar ke base64 untuk dikirim via JSON
            $base64QrCode = base64_encode($qrCodeImage);

            return response()->json([
                'success' => true,
                'barcode' => $base64QrCode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    public function scanQr($kode)
    {
        $nasabah = User::where('kode_nasabah', $kode)
            ->where('role', 'nasabah')
            ->first();

        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
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
                ->map(function($item) use ($nasabah) {
                    Carbon::setLocale('id');
                    $item->user_name = $nasabah ? $nasabah->name : 'Nasabah';
                    $item->tanggal_formatted = Carbon::parse($item->created_at)->translatedFormat('d F Y, H:i') . ' WIB';
                    $isMasuk = strtolower($item->jenis_transaksi) === 'masuk';
                    
                    // 🌟 PERBAIKAN DI SINI: Judul dinamis disesuaikan dengan Status Transaksi
                    if ($isMasuk) {
                        $item->judul_dinamis = 'Setor Sampah Nasabah';
                        $item->total_berat = 'Lihat Detail';
                        
                        // Load setor_sampah details and courier info
                        if ($item->sumber === 'setor_sampah' && !empty($item->referensi_id)) {
                            $setor = \App\Models\SetorSampah::with(['kurir', 'details.jenisSampah'])->find($item->referensi_id);
                            if ($setor) {
                                $item->kurir = $setor->kurir;
                                $item->nama_kurir = $setor->kurir ? $setor->kurir->name : null;
                                $item->details = $setor->details;
                                $item->jenis_sampah = $setor->jenis_sampah;
                                
                                $totalBerat = $setor->details->sum('berat');
                                $item->total_berat = $totalBerat;
                            }
                        }
                    } else {
                        // Jika transaksi keluar (Tarik Tunai), cek statusnya
                        $status = strtolower($item->status ?? 'pending');
                        if ($status === 'pending') {
                            $item->judul_dinamis = 'Tarik Tunai Dana (Pending)';
                        } elseif ($status === 'rejected') {
                            $item->judul_dinamis = 'Tarik Tunai Dana (Ditolak)';
                        } else {
                            $item->judul_dinamis = 'Tarik Tunai Dana'; // Jika sukses/approved
                        }
                        $item->total_berat = '0';
                    }
                    
                    return $item;
                });
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
                    'kode_nasabah' => $nasabah->kode_nasabah, // ➕ TAMBAHAN: Kirim kode nasabah untuk QR Code
                    'saldo_aktif' => (int) ($nasabah->saldo ?? 0),
                    'saldo_pending' => (int) $saldoPending,
                    'saldo' => (int) ($nasabah->saldo ?? 0),
                    'total_berat_kg' => round((double)$totalBeratSampah, 1),
                    'has_pin' => !empty($nasabah->pin_hash),
                    'foto' => $nasabah->foto ? url($nasabah->foto) : null, // 🎨 PERBAIKAN: Kirim URL lengkap untuk foto
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

    private function verifyTransactionPin($user, $pin)
    {
        if ($user->pin_locked_until && now()->lessThan($user->pin_locked_until)) {
            $diff = now()->diffInMinutes($user->pin_locked_until);
            throw new \Exception("PIN terblokir sementara. Coba lagi dalam $diff menit.");
        }

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

        $user->update(['pin_attempts' => 0, 'pin_locked_until' => null]);
        return true;
    }

    /**
     * Endpoint API untuk memuat riwayat lengkap nasabah di mobile (Pending, Approved, Rejected)
     */
    public function riwayatLengkapMobile(Request $request)
    {
        $user = $request->user();

        // Mengambil semua riwayat mutasi tarik tunai milik nasabah tersebut
        $riwayat = DB::table('mutasi_saldos')
            ->where('user_id', $user->id)
            ->where('sumber', 'tarik_tunai')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($item) {
                // Pastikan status dikirim dalam format teks kecil agar seragam dibaca Flutter
                $item->status = strtolower($item->status ?? 'pending');
                return $item;
            });

        return response()->json([
            'success' => true,
            'data' => $riwayat
        ], 200);
    }

    /**
     * PROSES UTAMA: Pengajuan penarikan berstatus pending & Saldo utuh di awal
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
                        $fail('Nominal penarikan harus kelipatan Rp 500.');
                    }
                },
            ],
            'metode'   => 'required',
            'nomor_hp' => 'required',
        ]);

        $user = $request->user();
        if ($user->id != $request->user_id) {
            return response()->json(['success' => false, 'message' => 'ID Nasabah tidak valid.'], 403);
        }

        try {
            if ($user->saldo < $request->nominal) {
                return response()->json(['success' => false, 'message' => 'Saldo tidak mencukupi.'], 400);
            }

            DB::beginTransaction();

            $externalId = 'WD-' . time() . '-' . $user->id;

            // 1. Masuk ke tabel tarik_tunais agar terdeteksi oleh Web Admin
            TarikTunai::create([
                'user_id' => $user->id,
                'jumlah_nominal' => $request->nominal,
                'status' => 'pending',
                'metode' => $request->metode, 
                'nomor_hp' => $request->nomor_hp,
                'tanggal_request' => now(), // 🔥 TAMBAHKAN BARIS INI agar tidak null lagi!
            ]);

            // 2. Catat ke Mutasi Saldo dengan STATUS PENDING untuk histori di HP
            $mutasi = new MutasiSaldo();
            $mutasi->user_id = $user->id;
            $mutasi->jenis_transaksi = 'keluar';
            $mutasi->sumber = 'tarik_tunai';
            $mutasi->nominal = $request->nominal;
            $mutasi->status = 'pending'; 
            $mutasi->keterangan = "Penarikan via {$request->metode} ke {$request->nomor_hp} (Menunggu Persetujuan)";
            $mutasi->save();

            // Saldo tidak disentuh sama sekali di sini

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan penarikan berhasil dikirim. Menunggu persetujuan admin.',
                'transaction_id' => $externalId,
                'saldo_terakhir' => $user->saldo
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    /**
     * 🔒 Endpoint untuk proses logout yang aman dari aplikasi mobile.
     * Mencabut (revoke) token yang sedang digunakan.
     */
    public function logout(Request $request)
    {
        try {
            // Menghapus token yang digunakan untuk request ini
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Logout berhasil.'
            ]);

        } catch (\Exception $e) {
            // Jika terjadi error (misalnya user tidak terotentikasi), kirim response error
            return response()->json(['success' => false, 'message' => 'Gagal melakukan logout di server.'], 500);
        }
    }
}