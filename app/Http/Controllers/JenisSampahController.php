<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use Illuminate\Http\Request;

class JenisSampahController extends Controller
{

    // ambil semua data
    public function index()
    {
        return response()->json(
            JenisSampah::all()
        );
    }


    // tambah data
    public function store(Request $request)
    {
        $request->validate([
            'bank_sampah_id' => 'required',
            'nama' => 'required',
            'kode_icon' => 'required',
            'harga_per_kg' => 'required|numeric'
        ]);


        $jenis = JenisSampah::create([
            'bank_sampah_id' => $request->bank_sampah_id,
            'nama' => $request->nama,
            'kode_icon' => $request->kode_icon,
            'harga_per_kg' => $request->harga_per_kg,
        ]);


        return response()->json([
            'message' => 'Berhasil tambah',
            'data' => $jenis
        ]);
    }


    // detail
    public function show($id)
    {
        return response()->json(
            JenisSampah::findOrFail($id)
        );
    }


    // update
    public function update(Request $request, $id)
    {
        $request->validate([
            'bank_sampah_id' => 'required',
            'nama' => 'required',
            'kode_icon' => 'required',
            'harga_per_kg' => 'required|numeric'
        ]);


        $jenis = JenisSampah::findOrFail($id);


        $jenis->update([
            'bank_sampah_id' => $request->bank_sampah_id,
            'nama' => $request->nama,
            'kode_icon' => $request->kode_icon,
            'harga_per_kg' => $request->harga_per_kg,
        ]);


        return response()->json([
            'message' => 'Berhasil update',
            'data' => $jenis
        ]);
    }


    // hapus
    public function destroy($id)
    {
        $jenis = JenisSampah::findOrFail($id);

        $jenis->delete();

        return response()->json([
            'message' => 'Berhasil hapus'
        ]);
    }
}