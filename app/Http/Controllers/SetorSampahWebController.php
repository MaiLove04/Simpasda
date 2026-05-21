<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah;

class SetorSampahWebController extends Controller
{
    public function index()
    {
        // Mengambil data setor sampah beserta relasi nasabah (user) dan jenis sampah
        $dataSetor = SetorSampah::with(['user', 'jenisSampah'])->latest()->get();

        return view('admin.setor-sampah.index', compact('dataSetor'));
    }
}