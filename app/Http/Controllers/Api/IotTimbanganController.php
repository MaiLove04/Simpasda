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
     * IoT mengirim data, sistem akan meng-update record yang ada 
     * atau membuat baru jika belum ada.
     */
    public function updateBerat(Request $request)
    {
        // Validasi data
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

        // Tentukan ID perangkat, default ke 'Alat-IoT-01'
        $deviceId = $request->device_id ?? 'Alat-IoT-01';

        // Menggunakan updateOrCreate agar data tidak menumpuk
        $timbangan = BeratTimbangan::updateOrCreate(
            ['device_id' => $deviceId], // Mencari berdasarkan device_id
            ['berat' => $request->berat] // Update kolom berat
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Berat berhasil diperbarui!',
            'data' => $timbangan
        ], 200);
    }

    /**
     * 2. ENDPOINT UNTUK FLUTTER (HTTP GET)
     */
    public function getBeratTerakhir()
    {
        // Ambil data terbaru berdasarkan update terakhir
        $timbanganTerakhir = BeratTimbangan::latest('updated_at')->first();

        $berat = $timbanganTerakhir ? $timbanganTerakhir->berat : 0.0;

        return response()->json([
            'status' => 'success',
            'berat_iot' => $berat,
            'updated_at' => $timbanganTerakhir ? $timbanganTerakhir->updated_at->toIso8601String() : now()->toIso8601String()
        ], 200);
    }
}