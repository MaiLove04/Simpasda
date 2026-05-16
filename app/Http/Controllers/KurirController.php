<?php

namespace App\Http\Controllers;

use App\Models\SetorSampah;

class KurirController extends Controller
{
    public function dashboard_kurir()
    {
        $pesanan = SetorSampah::latest()
                    ->take(1)
                    ->get();

        $totalPesanan =
        SetorSampah::count();

        return response()->json([
            'total_pesanan' => $totalPesanan,
            'pesanan_berikutnya' => $pesanan
        ]);
    }
}