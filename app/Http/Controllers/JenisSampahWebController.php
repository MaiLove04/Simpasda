<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use Illuminate\Http\Request;

class JenisSampahWebController extends Controller
{
    // ================= INDEX =================
    public function index()
    {
        $jenisSampahs = JenisSampah::all();

        return view(
            'admin.jenis_sampah.index',
            compact('jenisSampahs')
        );
    }

    // ================= CREATE =================
    public function create()
    {
        return view('admin.jenis_sampah.create');
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $request->validate([

            'nama' => 'required',

            // 'kode_icon' => 'required',

            'harga_per_kg' => 'required|numeric',

            //'poin_per_kg' => 'required|numeric',

            'status' => 'required',

        ]);

        JenisSampah::create([

            'bank_sampah_id' => auth()->user()->bank_sampah_id,

            'nama' => $request->nama,

            //'kode_icon' => $request->kode_icon,

            'harga_per_kg' => $request->harga_per_kg,

            //'poin_per_kg' => $request->poin_per_kg,

            'status' => $request->status,

        ]);

        return redirect('/admin/jenis-sampah')
            ->with('success', 'Jenis sampah berhasil ditambahkan');
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $item = JenisSampah::findOrFail($id);

        return view(
            'admin.jenis_sampah.edit',
            compact('item')
        );
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $item = JenisSampah::findOrFail($id);

        $item->update([

            'nama' => $request->nama,

           // 'kode_icon' => $request->kode_icon,

            'harga_per_kg' => $request->harga_per_kg,

            //'poin_per_kg' => $request->poin_per_kg,

            'status' => $request->status,

        ]);

        return redirect('/admin/jenis-sampah')
            ->with('success', 'Jenis sampah berhasil diupdate');
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $item = JenisSampah::findOrFail($id);

        $item->delete();

        return redirect('/admin/jenis-sampah')
            ->with('success', 'Jenis sampah berhasil dihapus');
    }
}