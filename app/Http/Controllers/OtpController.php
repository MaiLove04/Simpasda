<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OtpController extends Controller
{
    /**
     * Mengirimkan kode OTP ke nomor WhatsApp pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,no_hp',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Nomor HP tidak terdaftar.'], 404);
        }

        $phone = $request->input('phone');

        // Generate 4 digit OTP
        $otpCode = rand(1000, 9999);

        // Simpan atau perbarui OTP di database dengan masa berlaku 5 menit
        DB::table('password_resets')->updateOrInsert(
            ['phone' => $phone],
            [
                'otp_code' => $otpCode,
                'expired_at' => now()->addMinutes(5)
            ]
        );

        // Kirim OTP via WhatsApp Gateway
        $this->sendWhatsAppOtp($phone, $otpCode);

        return response()->json([
            'success' => true,
            'message' => 'OTP telah dikirim ke nomor WhatsApp Anda.'
        ]);
    }

    /**
     * Verifikasi OTP dan reset password pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtpAndReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,no_hp',
            'otp_code' => 'required|string|digits:4',
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $phone = $request->input('phone');
        $otpCode = $request->input('otp_code');

        // Cari record OTP
        $otpRecord = DB::table('password_resets')->where('phone', $phone)->first();

        // Validasi OTP
        if (!$otpRecord || $otpRecord->otp_code != $otpCode) {
            return response()->json(['success' => false, 'message' => 'Kode OTP yang Anda masukkan salah.'], 400);
        }

        // Validasi masa berlaku OTP
        if (now()->isAfter($otpRecord->expired_at)) {
            DB::table('password_resets')->where('phone', $phone)->delete();
            return response()->json(['success' => false, 'message' => 'OTP telah kedaluwarsa. Silakan request ulang.'], 400);
        }

        // Update password user
        $user = User::where('no_hp', $phone)->first();
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        // Hapus OTP yang sudah digunakan
        DB::table('password_resets')->where('phone', $phone)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password Anda berhasil direset. Silakan login kembali.'
        ]);
    }

    /**
     * Placeholder untuk mengirim OTP via WhatsApp Gateway.
     * Ganti dengan implementasi API Gateway Anda (Fonnte, Wablas, Twilio, dll).
     */
    private function sendWhatsAppOtp(string $targetNumber, string $otpCode): void
    {
        $message = "Kode OTP Anda untuk reset password di Bank Sampah ASRI adalah: *$otpCode*. Jangan berikan kode ini kepada siapapun. Kode berlaku selama 5 menit.";

        // --- CONTOH INTEGRASI DENGAN FONNTE ---
        // $fonnteToken = env('FONNTE_API_TOKEN');
        // Http::withHeaders(['Authorization' => $fonnteToken])
        //     ->post('https://api.fonnte.com/send', ['target' => $targetNumber, 'message' => $message]);

        // Untuk development, kita simpan ke log
        Log::info("Mengirim OTP ke {$targetNumber}: {$message}");
    }
}