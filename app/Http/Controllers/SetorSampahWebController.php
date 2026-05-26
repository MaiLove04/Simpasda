<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah;

class SetorSampahWebController extends Controller
{
    /**
     * MENAMPILKAN LOG PENYETORAN SAMPAH + FITUR SEARCH & PAGINATION (MAKSIMAL 7 DATA)
     */
    public function index(Request $request)
    {
        // Ambil kata kunci pencarian dari input bernama 'search'
        $keyword = $request->get('search');

        // Query data beserta relasinya dengan pencarian & pagination maksimal 7 data
        $dataSetor = SetorSampah::with(['nasabah', 'kurir', 'details.jenisSampah'])
            ->when($keyword, function ($query, $keyword) {
                return $query->whereHas('nasabah', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%');
                })->orWhereHas('kurir', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%');
                });
            })
            ->latest()
            ->paginate(7); // 📑 KUNCI UTAMA: Diubah menjadi 7 data per halaman

        return view('admin.setor-sampah.index', compact('dataSetor'));
    }

    /**
     * 📟 AMBIL BERAT TERBARU DARI TIMBANGAN IOT (SIMULASI/AKTUAL)
     */
    public function getBeratIot()
    {
        $berat = rand(5, 15) + (rand(0, 9) / 10);
        
        return response()->json([
            'success' => true,
            'berat_iot' => $berat
        ], 200);
    }
}