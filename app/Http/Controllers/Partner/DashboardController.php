<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PengirimanMitra;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $mitra = Auth::user()->mitra;

        $menunggu = PengirimanMitra::where('mitra_id',$mitra->id)
                        ->where('status_pengiriman','Menunggu Mitra')
                        ->count();

        $diterima = PengirimanMitra::where('mitra_id',$mitra->id)
                        ->where('status_pengiriman','Diterima')
                        ->count();

        $belumBayar = PengirimanMitra::where('mitra_id',$mitra->id)
                        ->where('status_pembayaran','Belum Bayar')
                        ->count();

        $lunas = PengirimanMitra::where('mitra_id',$mitra->id)
                        ->where('status_pembayaran','Lunas')
                        ->count();

        $pengiriman = PengirimanMitra::where('mitra_id',$mitra->id)
                        ->latest()
                        ->take(5)
                        ->get();

        return view('partner.dashboard',compact(
            'menunggu',
            'diterima',
            'belumBayar',
            'lunas',
            'pengiriman'
        ));
    }
}