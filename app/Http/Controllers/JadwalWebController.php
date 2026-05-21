<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JadwalPenjemputan; // Menggunakan model penjemputan asli kamu

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalWebController extends Controller
{
    public function index()
    {
        $jadwals = JadwalPenjemputan::with('nasabah', 'kurir')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->latest()
            ->get();

        return view('admin.jadwal.index', compact('jadwals'));
    }

    public function create()
    {
        $nasabahs = User::where('role', 'nasabah')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->get();

        $kurirs = User::where('role', 'kurir')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->get();

        return view('admin.jadwal.create', compact('nasabahs', 'kurirs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nasabah_id'          => 'required',
            'kurir_id'            => 'required',
            'tanggal_penjemputan' => 'required',
            'alamat'              => 'required',
        ]);

        JadwalPenjemputan::create([
            'bank_sampah_id'      => Auth::user()->bank_sampah_id,
            'nasabah_id'          => $request->nasabah_id,
            'kurir_id'            => $request->kurir_id,
            'tanggal_penjemputan' => $request->tanggal_penjemputan,
            'alamat'              => $request->alamat,
            'catatan'             => $request->catatan,
            'status'              => 'terjadwal',
        ]);

        return redirect('/admin/jadwal');
    }

    // ========================================================
    // PERBAIKAN: Mengubah Jadwal menjadi JadwalPenjemputan
    // ========================================================
    public function jadwalKurir($id)
    {
        $jadwal = JadwalPenjemputan::with(['nasabah', 'kurir'])
            ->where('kurir_id', $id)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $jadwal
        ]);
    }

    //mulai penjemputan
    public function mulaiJemput($id)
    {
        $jadwal = JadwalPenjemputan::find($id);

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan'
            ], 404);
        }

        // Ubah status menjadi proses
        $jadwal->status = 'proses';
        $jadwal->save();

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui menjadi proses',
            'data' => $jadwal
        ]);
    }
}