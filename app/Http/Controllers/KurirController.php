<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JadwalPenjemputan;

class KurirController extends Controller
{
    public function dashboard_kurir($id)
    {
        $jadwal = JadwalPenjemputan::with('kurir')
            ->where('kurir_id', $id)
            ->latest()
            ->first();

        $totalPesanan =
            JadwalPenjemputan::where(
                'kurir_id',
                $id
            )->count();

        return response()->json([

            'total_pesanan' =>
                $totalPesanan,

            'nama_kurir' =>
                User::find($id)->name ?? 'Kurir',

            'jadwal' =>
                $jadwal,
        ]);
    }
}