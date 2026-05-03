<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 🔹 REGISTER
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'alamat' => 'required',
            'no_hp' => 'required|unique:users'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'role' => 'nasabah',

            // 🔥 UBAH INI BIAR BISA LOGIN
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Register berhasil',
            'user' => $user
        ], 201);
    }

    // 🔹 LOGIN
    public function login(Request $request)
    {
        // 🔥 VALIDASI
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        // 🔥 CEK USER & PASSWORD
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // 🔥 CEK STATUS
        if ($user->status != 'aktif') {
        return response()->json([
            'message' => 'Akun menunggu verifikasi bank sampah'
        ], 403);
    }

        // 🔥 BUAT TOKEN
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }

    // 🔹 LOGOUT
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}