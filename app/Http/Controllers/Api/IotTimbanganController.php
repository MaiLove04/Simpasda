<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BeratTimbangan;
use Illuminate\Support\Facades\Validator;

class IotTimbanganController extends Controller
{
    /**
     * 1. ENDPOINT UNTUK IOT (HTTP POST)
     * IoT akan mengirim data ke sini
     */
    public function updateBerat(Request $request)
    {
        // Validasi data yang masuk dari IoT
        $validator = Validator::make($request->all(), [
            'berat' => 'required|numeric',
            'device_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // Simpan data berat baru ke database
        $timbangan = BeratTimbangan::create([
            'berat' => $request->berat,
            'device_id' => $request->device_id ?? 'Alat-IoT-01'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berat berhasil diperbarui oleh IoT!',
            'data' => $timbangan
        ], 200);
    }

    /**
     * 2. ENDPOINT UNTUK FLUTTER (HTTP GET)
     * Aplikasi kurir Flutter mengambil data terbaru dari sini
     */
    public function getBeratTerakhir()
    {
        // Ambil data timbangan yang paling terakhir dimasukkan
        $timbanganTerakhir = BeratTimbangan::latest()->first();

        // Jika tabel masih kosong, kembalikan nilai 0
        $berat = $timbanganTerakhir ? $timbanganTerakhir->berat : 0.0;

        return response()->json([
            'status' => 'success',
            'berat_iot' => $berat,
            'updated_at' => $timbanganTerakhir ? $timbanganTerakhir->created_at->toIso8601String() : now()->toIso8601String()
        ], 200);
    }
}
