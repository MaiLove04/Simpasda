<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ================= REGISTER =================
    public function register(
        Request $request
    ) {

        $request->validate([

            'name' =>
                'required',

            'email' =>
                'required|email|unique:users',

            'password' =>
                'required|min:6|confirmed',

            'alamat' =>
                'required',

            'no_hp' =>
                'required|unique:users',

            'foto' =>
                'nullable|image',

            'bank_sampah_id' =>
                'required|exists:bank_sampahs,id',
        ]);


        $foto = null;


        if (
            $request->hasFile(
                'foto'
            )
        ) {

            $file =
                $request->file(
                    'foto'
                );


            $fileName =

                time() .

                '_' .

                $file
                    ->getClientOriginalName();


            $file->move(

                public_path(
                    'uploads/users'
                ),

                $fileName,
            );


            $foto =

                'uploads/users/' .

                $fileName;
        }

        $jumlahNasabah = User::where('role', 'nasabah')->count() + 1;

        $kodeNasabah = 'NSB' . str_pad($jumlahNasabah, 3, '0', STR_PAD_LEFT);

        $user = User::create([

            'name' =>
                $request->name,

            'email' =>
                $request->email,

            'password' =>
                bcrypt(
                    $request->password
                ),

            'alamat' =>
                $request->alamat,

            'no_hp' =>
                $request->no_hp,

            'role' =>
                'nasabah',

            'status' =>
                'pending',

            'foto' =>
                $foto,

            'bank_sampah_id' =>
                $request->bank_sampah_id,

            'kode_nasabah' =>
                $kodeNasabah,
        ]);


        return response()->json([

            'message' =>
                'Register berhasil',

            'user' =>
                $user,

        ], 201);
    }



    // ================= LOGIN =================
    public function login(
        Request $request
    ) {

        $request->validate([

            'email' =>
                'required|email',

            'password' =>
                'required',
        ]);


        $user = User::where(
            'email',
            $request->email,
        )->first();


        if (

            !$user ||

            !Hash::check(
                $request->password,
                $user->password,
            )

        ) {

            return response()->json([

                'message' =>
                    'Email atau password salah',

            ], 401);
        }


        if (

            $user->status !=
            'aktif'

        ) {

            return response()->json([

                'message' =>
                    'Akun belum disetujui admin',

            ], 403);
        }


        $token =

            $user

                ->createToken(
                    'auth_token'
                )

                ->plainTextToken;


        return response()->json([

            'message' =>
                'Login berhasil',

            'token' =>
                $token,

            'user' => [

                'id' =>
                    $user->id,

                'name' =>
                    $user->name,

                'email' =>
                    $user->email,

                'role' =>
                    $user->role,

                'foto' =>

                    $user->foto

                    ?

                    asset(
                        $user->foto
                    )

                    :

                    null,
            ],
        ]);
    }

// ================= LOGIN WEB =================
public function createAdmin(
    Request $request
) {

    $request->validate([

        'name' =>
            'required',

        'email' =>
            'required|email|unique:users',

        'password' =>
            'required|min:6',

        'alamat' =>
            'required',

        'no_hp' =>
            'required|unique:users',

        'bank_sampah_id' =>
            'required|exists:bank_sampahs,id',
    ]);


    $admin = User::create([

        'name' =>
            $request->name,

        'email' =>
            $request->email,

        'password' =>
            bcrypt(
                $request->password
            ),

        'alamat' =>
            $request->alamat,

        'no_hp' =>
            $request->no_hp,

        'role' =>
            'admin_bank',

        'status' =>
            'aktif',

        'bank_sampah_id' =>
            $request->bank_sampah_id,
    ]);


    return response()->json([

        'message' =>
            'Admin berhasil dibuat',

        'data' =>
            $admin,
    ], 201);
}
}