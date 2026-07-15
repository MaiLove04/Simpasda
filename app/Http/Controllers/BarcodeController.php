<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Milon\Barcode\Facades\DNS2DFacade;

class BarcodeController extends Controller
{
    public function barcodeNasabah($id)
    {
        $nasabah = User::find($id);

        // Jika nasabah tidak ditemukan atau kode belum ada, kembalikan response error
        if (!$nasabah || !$nasabah->kode_nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah atau kode nasabah tidak ditemukan.'
            ], 404);
        }

        // Generate QR Code menggunakan kode_nasabah (lebih aman daripada ID)
        $barcodeData = DNS2DFacade::getBarcodePNG(
            $nasabah->kode_nasabah, 
            'QRCODE'
        );

        return response()->json([
            'success' => true,
            'id' => $id, 
            'barcode' => base64_encode($barcodeData), // 🔥 PERBAIKAN: Encode data gambar ke Base64
        ]);
    }
}