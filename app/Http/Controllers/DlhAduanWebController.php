<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aduan;

class DlhAduanWebController extends Controller
{
    public function index()
    {
        $aduans = Aduan::with('user')->latest()->paginate(10);
        return view('dlh.aduan.index', compact('aduans'));
    }

    public function show($id)
    {
        $aduan = Aduan::with('user')->findOrFail($id);
        return view('dlh.aduan.show', compact('aduan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:menunggu,diproses,selesai',
            'tanggapan' => 'nullable|string'
        ]);

        $aduan = Aduan::findOrFail($id);
        $aduan->update([
            'status' => $request->status,
            'tanggapan' => $request->tanggapan
        ]);

        return redirect()->route('dlh.aduan.index')->with('success', 'Tanggapan berhasil dikirim dan status diperbarui!');
    }
}