<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PengirimanMitra;

class PembayaranController extends Controller
{
    public function index()
    {
        $mitra = Auth::user()->mitra;

        $pembayaran = PengirimanMitra::where('mitra_id', $mitra->id)
            ->whereNotNull('tanggal_pembayaran')
            ->latest('tanggal_pembayaran')
            ->paginate(10);

        return view('partner.pembayaran.index', compact('pembayaran'));
    }
}