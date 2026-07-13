<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PengirimanMitra;

class PengirimanController extends Controller
{
    public function index()
{
    $mitra = Auth::user()->mitra;

    $pengiriman = PengirimanMitra::with('details')
                    ->where('mitra_id', $mitra->id)
                    ->latest()
                    ->paginate(10);

    return view(
        'partner.pengiriman.index',
        compact('pengiriman')
    );
}

    public function show($id)
    {
        $mitra = Auth::user()->mitra;

        $data = PengirimanMitra::with('details')
                    ->where('mitra_id',$mitra->id)
                    ->findOrFail($id);

        return view(
            'partner.pengiriman.show',
            compact('data')
        );
    }

    public function terima($id)
    {
        $mitra = Auth::user()->mitra;

        $pengiriman = PengirimanMitra::where('mitra_id', $mitra->id)
                        ->findOrFail($id);

        $pengiriman->update([
            'status_pengiriman' => 'Diterima'
        ]);

        return back()->with(
            'success',
            'Barang berhasil dikonfirmasi diterima.'
        );
    }

    public function pembayaran(Request $request, $id)
    {
        $mitra = Auth::user()->mitra;

        $pengiriman = PengirimanMitra::where('mitra_id',$mitra->id)
                        ->findOrFail($id);

        $request->validate([
            'metode_pembayaran'=>'required',
            'bukti_transfer'=>'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $bukti = null;

        if($request->hasFile('bukti_transfer')){
            $bukti = $request
                        ->file('bukti_transfer')
                        ->store('bukti-transfer','public');
        }

        $pengiriman->update([
            'metode_pembayaran'=>$request->metode_pembayaran,
            'bukti_transfer'=>$bukti,
            'tanggal_pembayaran'=>now(),
            'status_pembayaran'=>'Menunggu Verifikasi'
        ]);

        return back()->with(
            'success',
            'Pembayaran berhasil dikirim.'
        );
    }
}