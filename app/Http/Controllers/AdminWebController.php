<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminWebController extends Controller
{

    public function showLogin()
    {
        if (Auth::check()) {

            return redirect(
                '/admin/dashboard'
            );
        }

        return view(
            'admin.login'
        );
    }



    public function login(Request $request)
    {

        $request->validate([

            'email' =>
                'required',

            'password' =>
                'required',
        ]);


        $credentials = [

            'email' =>
                $request->email,

            'password' =>
                $request->password,
        ];



        if (
            Auth::attempt(
                $credentials
            )
        ) {

            $user = Auth::user();



            if (
                $user->role
                !== 'admin_bank'
            ) {

                Auth::logout();

                return back()->with(
                    'error',
                    'Bukan admin bank sampah'
                );
            }



            $request
                ->session()
                ->regenerate();



            return redirect(
                '/admin/dashboard'
            );
        }



        return back()->with(
            'error',
            'Email atau password salah'
        );
    }



    public function dashboard()
    {
        return view(
            'admin.dashboard'
        );
    }



    public function logout(Request $request)
    {

        Auth::logout();


        $request
            ->session()
            ->invalidate();


        $request
            ->session()
            ->regenerateToken();


        return redirect(
            '/admin/login'
        );
    }
}