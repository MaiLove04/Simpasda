<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Milon\Barcode\Facades\DNS2DFacade;

class BarcodeController extends Controller
{
    public function barcodeNasabah($id)
    {
        // 1. Cari nasabah berdasarkan ID untuk mendapatkan kode nasabahnya
        $nasabah = User::find($id);

        if (!$nasabah || !$nasabah->kode_nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah atau kode nasabah tidak ditemukan.'
            ], 404);
        }

        // 2. Generate QR Code menggunakan 'kode_nasabah' (contoh: NSB001)
        $barcode = DNS2DFacade::getBarcodePNG(
            $nasabah->kode_nasabah, // Menggunakan kode_nasabah, bukan ID
            'QRCODE'
        );

        return response()->json([
            'id' => $id, // ID tetap dikirim untuk referensi
            'barcode' => $barcode,
        ]);
    }
}
