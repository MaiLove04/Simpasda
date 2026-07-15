<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Langkah 1: Meminta pengiriman OTP ke nomor WhatsApp.
     */
    public function requestOtp(Request $request)
    {
        $request->validate(['no_hp' => 'required|string']);

        $user = User::where('no_hp', $request->no_hp)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Nomor HP tidak terdaftar.'], 404);
        }

        // Generate OTP 6 digit
        $otp = rand(100000, 999999);

        // Simpan OTP ke database (tabel password_resets)
        DB::table('password_resets')->updateOrInsert(
            ['identifier' => $user->no_hp],
            [
                'token' => $otp,
                'created_at' => now()
            ]
        );

        // Kirim OTP via WhatsApp (simulasi)
        $this->sendWhatsAppOtp($user->no_hp, $otp);

        return response()->json([
            'success' => true,
            'message' => 'OTP telah dikirim ke nomor WhatsApp Anda.'
        ]);
    }

    /**
     * Langkah 2: Mereset password dengan OTP yang valid.
     */
    public function resetWithOtp(Request $request)
    {
        $request->validate([
            'no_hp' => 'required|string',
            'otp' => 'required|string|digits:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Cari record OTP di database
        $resetRecord = DB::table('password_resets')
            ->where('identifier', $request->no_hp)
            ->first();

        // 1. Validasi apakah OTP ada
        if (!$resetRecord) {
            return response()->json(['success' => false, 'message' => 'OTP tidak valid atau belum di-request.'], 400);
        }

        // 2. Validasi apakah OTP sudah kedaluwarsa (misal: 10 menit)
        $otpCreatedAt = Carbon::parse($resetRecord->created_at);
        if ($otpCreatedAt->addMinutes(10)->isPast()) {
            // Hapus OTP yang sudah kedaluwarsa
            DB::table('password_resets')->where('identifier', $request->no_hp)->delete();
            return response()->json(['success' => false, 'message' => 'OTP telah kedaluwarsa. Silakan request ulang.'], 400);
        }

        // 3. Validasi kecocokan OTP
        if ($resetRecord->token !== $request->otp) {
            return response()->json(['success' => false, 'message' => 'Kode OTP yang Anda masukkan salah.'], 400);
        }

        // Jika semua validasi lolos, update password user
        $user = User::where('no_hp', $request->no_hp)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            // Hapus OTP yang sudah berhasil digunakan
            DB::table('password_resets')->where('identifier', $request->no_hp)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password Anda berhasil direset. Silakan login kembali.'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal mereset password. User tidak ditemukan.'], 500);
    }

    /**
     * Helper untuk mengirim OTP via WhatsApp.
     * Ganti dengan layanan WhatsApp Gateway Anda (misal: Fonnte, Zenziva, Twilio).
     */
    private function sendWhatsAppOtp($targetNumber, $otp)
    {
        $message = "Kode OTP Anda untuk reset password di Bank Sampah ASRI adalah: *$otp*. Jangan berikan kode ini kepada siapapun. Kode berlaku selama 10 menit.";

        // --- CONTOH INTEGRASI DENGAN FONNTE ---
        // $fonnteToken = env('FONNTE_API_TOKEN');
        // Http::withHeaders([
        //     'Authorization' => $fonnteToken,
        // ])->post('https://api.fonnte.com/send', [
        //     'target' => $targetNumber,
        //     'message' => $message,
        //     'countryCode' => '62', // Kode negara Indonesia
        // ]);

        // --- SIMULASI: Simpan ke log untuk development ---
        Log::info("Mengirim OTP ke {$targetNumber}: {$message}");

        return true;
    }
}