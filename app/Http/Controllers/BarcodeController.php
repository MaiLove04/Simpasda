<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Milon\Barcode\Facades\DNS2DFacade;

class BarcodeController extends Controller
{
   public function barcodeNasabah($id)
    {
        $barcode =
        DNS2DFacade::getBarcodePNG(
            $id,
            'QRCODE'
        );

        return response()->json([

            'id' => $id,

            'barcode' => $barcode,

        ]);
    }
}
