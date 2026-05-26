<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah;

class SetorSampahWebController extends Controller
{
    public function index()
    {
        $dataSetor = SetorSampah::with(['nasabah', 'jenis_sampah'])->latest()->get();

        return view('admin.setor-sampah.index', compact('dataSetor'));
    }
    public function getBeratIot()
{
    $berat = rand(5, 15) + (rand(0, 9) / 10);
    return response()->json([
        'success' => true,
        'berat_iot' => $berat
    ], 200);
}
}