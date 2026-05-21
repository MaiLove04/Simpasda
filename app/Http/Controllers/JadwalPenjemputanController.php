<?php

namespace App\Http\Controllers;

use App\Models\JadwalPenjemputan;
use Illuminate\Http\Request;

class JadwalPenjemputanController extends Controller
{


    // =========================
// JADWAL KURIR
// =========================
public function jadwalKurir($id)
{
    try {

        $jadwal =
        JadwalPenjemputan::with([

            'nasabah',
            'kurir',
            'bankSampah'

        ])

        ->where(
            'kurir_id',
            $id
        )

        ->latest()

        ->get();

        return response()->json([

            'success' => true,

            'message' =>
                'Data jadwal berhasil diambil',

            'data' => $jadwal

        ], 200);

    } catch (\Exception $e) {

        return response()->json([

            'success' => false,

            'message' =>
                'Gagal mengambil jadwal',

            'error' =>
                $e->getMessage(),

        ], 500);
    }
}
    // =========================
    // SIMPAN JADWAL PENJEMPUTAN
    // =========================
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
                => 'required|string',

            'catatan'
                => 'nullable|string',
        ]);

        try {

            $admin = auth()->user();

            $jadwal =
            JadwalPenjemputan::create([

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

            // ================= LOAD RELASI =================
            $jadwal->load([

                'nasabah',
                'kurir',
                'bankSampah'

            ]);

            return response()->json([

                'success' => true,

                'message' =>
                    'Jadwal berhasil dibuat',

                'data' => $jadwal,

            ], 201);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Gagal membuat jadwal',

                'error' =>
                    $e->getMessage(),

            ], 500);
        }
    }

}