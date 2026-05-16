<?php

namespace App\Http\Controllers;

use App\Models\JadwalPenjemputan;
use Illuminate\Http\Request;

class JadwalPenjemputanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([

            'nasabah_id'
                => 'required|exists:users,id',

            'kurir_id'
                => 'required|exists:users,id',

            'tanggal_penjemputan'
                => 'required|date',

            'alamat'
                => 'required',

            'catatan'
                => 'nullable',
        ]);

        $admin = auth()->user();

        $jadwal = JadwalPenjemputan::create([

            'bank_sampah_id'
                => $admin->bank_sampah_id,

            'nasabah_id'
                => $request->nasabah_id,

            'kurir_id'
                => $request->kurir_id,

            'tanggal_penjemputan'
                => $request->tanggal_penjemputan,

            'alamat'
                => $request->alamat,

            'catatan'
                => $request->catatan,

            'status'
                => 'terjadwal',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dibuat',
            'data' => $jadwal,
        ], 201);
    }
}