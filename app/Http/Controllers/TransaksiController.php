<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;

class TransaksiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_sampah' => 'required',
            'catatan' => 'nullable',
        ]);

        $transaksi = Transaksi::create([
            'user_id' => 1, // testing dulu
            'jenis_sampah' => $validated['jenis_sampah'],
            'catatan' => $validated['catatan'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Transaksi berhasil dibuat',
            'data' => $transaksi
        ]);
    }
}