<?php

namespace App\Http\Controllers;

use App\Models\MasterJadwalRutin;
use App\Models\User; 
use Illuminate\Http\Request;

class MasterJadwalRutinController extends Controller
{
    public function index()
    {
        $masterJadwals = MasterJadwalRutin::with(['nasabah', 'kurir'])->get();
        return view('admin.master_jadwal.index', compact('masterJadwals'));
    }

    public function create()
    {
        $nasabahs = User::where('role', 'nasabah')->get();
        $kurirs = User::where('role', 'kurir')->get();
        $hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return view('admin.master_jadwal.create', compact('nasabahs', 'kurirs', 'hariOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required',
            'kurir_id' => 'required',
            'hari_penjemputan' => 'required',
            'jam_estimasi' => 'required',
        ]);

        MasterJadwalRutin::create([
            'nasabah_id' => $request->nasabah_id,
            'kurir_id' => $request->kurir_id,
            'hari_penjemputan' => $request->hari_penjemputan,
            'jam_estimasi' => $request->jam_estimasi,
            'is_aktif' => true
        ]);

        return redirect()->route('master-jadwal.index')->with('success', 'Pola penjemputan rutin berhasil ditambahkan!');
    }

    // Menampilkan Form Edit Pola
    public function edit($id)
    {
        $master = MasterJadwalRutin::findOrFail($id);
        $nasabahs = User::where('role', 'nasabah')->get();
        $kurirs = User::where('role', 'kurir')->get();
        $hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return view('admin.master_jadwal.edit', compact('master', 'nasabahs', 'kurirs', 'hariOptions'));
    }

    // Memproses Pembaruan Pola ke Database
    public function update(Request $request, $id)
    {
        $request->validate([
            'nasabah_id' => 'required',
            'kurir_id' => 'required',
            'hari_penjemputan' => 'required',
            'jam_estimasi' => 'required',
        ]);

        $master = MasterJadwalRutin::findOrFail($id);
        $master->update([
            'nasabah_id' => $request->nasabah_id,
            'kurir_id' => $request->kurir_id,
            'hari_penjemputan' => $request->hari_penjemputan,
            'jam_estimasi' => $request->jam_estimasi,
            'is_aktif' => $request->has('is_aktif') ? true : false, // Untuk toggle aktif/nonaktif
        ]);

        return redirect()->route('master-jadwal.index')->with('success', 'Pola penjemputan rutin berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $pola = MasterJadwalRutin::findOrFail($id);
        $pola->delete();

        return redirect()->route('master-jadwal.index')->with('success', 'Pola penjemputan rutin berhasil dihapus!');
    }


}