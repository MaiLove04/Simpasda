<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthPartnerController extends Controller
{
    public function showLogin()
    {
        return view('partner.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if(Auth::attempt($credentials))
        {
            $request->session()->regenerate();

            if(Auth::user()->role != 'mitra')
            {
                Auth::logout();

                return back()->with(
                    'error',
                    'Akun ini bukan akun Partner.'
                );
            }

            return redirect()->route('partner.dashboard');
        }

        return back()->with(
            'error',
            'Email atau password salah.'
        );
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('partner.login');
    }
}