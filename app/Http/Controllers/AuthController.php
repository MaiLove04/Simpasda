<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * ==========================================
     * 1. REGISTER (KHUSUS NASABAH VIA MOBILE)
     * ==========================================
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'password'       => 'required|min:6|confirmed',
            'alamat'         => 'required|string',
            'no_hp'          => 'required|string|unique:users,no_hp',
            'foto'           => 'nullable|image|max:2048',
            'bank_sampah_id' => 'required|exists:bank_sampahs,id',
        ]);

        try {
            DB::beginTransaction();

            $pathFoto = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/users'), $fileName);
                $pathFoto = 'uploads/users/' . $fileName;
            }

            // Hitung kode nasabah otomatis
            $jumlahNasabah = User::where('role', 'nasabah')->count() + 1;
            $kodeNasabah = 'NSB' . str_pad($jumlahNasabah, 3, '0', STR_PAD_LEFT);

            $user = User::create([
                'name'           => $request->name,
                'email'          => $request->no_hp . '@asri.com', // Dummy email untuk validasi DB
                'password'       => bcrypt($request->password),
                'alamat'         => $request->alamat,
                'no_hp'          => $request->no_hp,
                'role'           => 'nasabah',
                'status'         => 'pending', // Wajib di-approve admin web
                'foto'           => $pathFoto,
                'bank_sampah_id' => $request->bank_sampah_id,
                'kode_nasabah'   => $kodeNasabah,
                'saldo'          => 0,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Registrasi berhasil! Silakan tunggu persetujuan admin.',
                'user'    => $user,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal melakukan registrasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================
     * 2. LOGIN (MOBILE - MENGGUNAKAN NO HP)
     * ==========================================
     */
    public function login(Request $request)
    {
        $request->validate([
            'no_hp'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan Nomor HP
        $user = User::where('no_hp', $request->no_hp)->first();

        // 1. Verifikasi akun & password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Nomor HP atau password salah.'
            ], 401);
        }

        // 2. Verifikasi status approval
        if ($user->status !== 'aktif') {
            return response()->json([
                'message' => 'Akun Anda belum aktif/disetujui oleh admin.'
            ], 403);
        }

        // 3. Generate Token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'no_hp'   => $user->no_hp, // Gunakan no_hp alih-alih email
                'role'    => $user->role,
                'foto'    => $user->foto ? asset($user->foto) : null,
                'saldo'   => $user->saldo ?? 0,
            ],
        ]);
    }

    /**
     * ==========================================
     * 3. LOGOUT (SANCTUM)
     * ==========================================
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }

    /**
     * ==========================================
     * 4. CREATE ADMIN (WEB - MENGGUNAKAN EMAIL)
     * ==========================================
     */
    public function createAdmin(Request $request)
    {
        $request->validate([
            'name'           => 'required|string',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|min:6',
            'alamat'         => 'required|string',
            'no_hp'          => 'required|string|unique:users,no_hp',
            'bank_sampah_id' => 'required|exists:bank_sampahs,id',
        ]);

        $admin = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => bcrypt($request->password),
            'alamat'         => $request->alamat,
            'no_hp'          => $request->no_hp,
            'role'           => 'admin_bank', // Atau admin_bank_sampah sesuai kebijakan
            'status'         => 'aktif',
            'bank_sampah_id' => $request->bank_sampah_id,
        ]);

        return response()->json([
            'message' => 'Admin berhasil dibuat',
            'data'    => $admin,
        ], 201);
    }
}
