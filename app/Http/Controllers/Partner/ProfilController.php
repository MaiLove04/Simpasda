<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilController extends Controller
{
    public function index()
    {
        return view('partner.profil.index');
    }

    public function update(Request $request, $id)
    {
        $mitra = Auth::user()->mitra;

        $request->validate([
            'nama' => 'required',
            'telepon' => 'nullable',
            'alamat' => 'nullable'
        ]);

    }
}