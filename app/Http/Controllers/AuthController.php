<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Memformat nomor HP agar konsisten (misal: 08 menjadi 628)
     */
    private function formatNoHp($noHp)
    {
        $noHp = preg_replace('/[^0-9]/', '', $noHp);
        if (substr($noHp, 0, 2) === '08') {
            return '628' . substr($noHp, 2);
        }
        return $noHp;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|string|email|unique:users,email',
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
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/users'), $fileName);
                $pathFoto = 'uploads/users/' . $fileName;
            }

            // Generate Kode Nasabah
            $latestNasabah = User::where('role', 'nasabah')
                                 ->where('kode_nasabah', 'LIKE', 'NSB%')
                                 ->orderBy(DB::raw('CAST(SUBSTRING(kode_nasabah, 4) AS UNSIGNED)'), 'desc')
                                 ->first();
            
            $nextNumber = $latestNasabah ? ((int) substr($latestNasabah->kode_nasabah, 3) + 1) : 1;
            $kodeNasabah = 'NSB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $user = User::create([
                'name'           => $request->name,
                'email'          => $request->email,
                'password'       => Hash::make($request->password), // Gunakan Hash::make
                'alamat'         => $request->alamat,
                'no_hp'          => $this->formatNoHp($request->no_hp), // Normalisasi
                'role'           => 'nasabah',
                'status'         => 'pending',
                'foto'           => $pathFoto,
                'bank_sampah_id' => $request->bank_sampah_id,
                'kode_nasabah'   => $kodeNasabah,
                'saldo'          => 0,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Registrasi berhasil! Silakan tunggu persetujuan admin.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // Tentukan field berdasarkan input
        $field = $request->has('email') ? 'email' : ($request->has('no_hp') ? 'no_hp' : null);
        
        if (!$field) {
            return response()->json(['message' => 'Harap masukkan Email atau Nomor HP.'], 422);
        }

        if ($field === 'no_hp') {
            $formattedNoHp = $this->formatNoHp($request->no_hp);
            $user = User::where('no_hp', $formattedNoHp)
                        ->orWhere('no_hp', $request->no_hp)
                        ->first();
        } else {
            $user = User::where('email', $request->email)->first();
        }

        // Validasi User & Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Kredensial salah.'], 401);
        }

        // Cek status
        if ($user->status !== 'aktif') {
            return response()->json(['message' => 'Akun belum aktif/disetujui admin.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'role'  => $user->role,
                'foto'  => $user->foto ? asset($user->foto) : null,
            ],
        ]);
    }
}