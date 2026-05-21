<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah; 
use App\Models\JadwalPenjemputan; // Tambahkan ini jika belum ada untuk memanggil model Jadwal

class SetorSampahController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi kiriman data dari Flutter (Tambahkan 'jadwal_id')
        $request->validate([
            'user_id'         => 'required',
            'kurir_id'        => 'required',
            'jenis_sampah_id' => 'required',
            'berat'           => 'required|numeric',
            'harga_per_kg'    => 'required|numeric',
            'total'           => 'required|numeric',
            'foto_sampah'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'jadwal_id'       => 'required', // Wajib dikirim dari Flutter agar tahu jadwal mana yang mau diselesaikan
        ]);

        // 2. Handle upload file foto sampah ke folder public/uploads/sampah
        $fotoPath = null;
        if ($request->hasFile('foto_sampah')) {
            $file = $request->file('foto_sampah');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/sampah'), $filename);
            $fotoPath = 'uploads/sampah/' . $filename;
        }

        // 3. Simpan data baru ke dalam database (Setor Sampah)
        $setor = new SetorSampah();
        $setor->user_id = $request->user_id;
        $setor->kurir_id = $request->kurir_id;
        $setor->jenis_sampah_id = $request->jenis_sampah_id;
        $setor->catatan = $request->catatan;
        $setor->berat = $request->berat;
        $setor->harga_per_kg = $request->harga_per_kg;
        $setor->total = $request->total;
        $setor->foto_sampah = $fotoPath; 
        $setor->status = 'selesai'; 
        $setor->save();

        // ==========================================================
        // 4. OTOMATISASI: Update status Jadwal Penjemputan jadi selesai
        // ==========================================================
        $jadwal = JadwalPenjemputan::find($request->jadwal_id);
        if ($jadwal) {
            $jadwal->status = 'selesai';
            $jadwal->save();
        }

        // 5. Kembalikan respon sukses JSON ke Flutter
        return response()->json([
            'success' => true,
            'message' => 'Setor sampah berhasil disimpan dan status jadwal diperbarui menjadi selesai!',
            'data' => $setor
        ], 201);
    }
    
}

    