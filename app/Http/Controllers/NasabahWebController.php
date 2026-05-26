<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NasabahWebController extends Controller
{
    /**
     * MENAMPILKAN DAFTAR NASABAH + FILTER SEARCH & PAGINATION (MAKSIMAL 7 DATA PER CABANG)
     */
    public function index(Request $request)
    {
        // Ambil kata kunci pencarian dari input search bar
        $keyword = $request->get('search');

        // Mengunci data berdasarkan Cabang Admin Login, Role Nasabah, Fitur Search, dan Paging 7 Data
        $nasabahs = User::where('role', 'nasabah')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->when($keyword, function ($query, $keyword) {
                // Kunci pencarian di dalam bracket khusus (grouping WHERE) agar tidak merusak validasi bank_sampah_id
                return $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%')
                      ->orWhere('kode_nasabah', 'LIKE', '%' . $keyword . '%');
                });
            })
            ->latest()
            ->paginate(7); // 📑 SINKRON: Maksimal 7 data per halaman dan membuang error firstItem()

        return view('admin.nasabah.index', compact('nasabahs'));
    }

    /**
     * MENAMPILKAN DETAIL DATA NASABAH
     */
    public function show($id)
    {
        $nasabah = User::findOrFail($id);

        return view('admin.nasabah.show', compact('nasabah'));
    }

    /**
     * APPROVAL INSTAN AKUN NASABAH MENJADI AKTIF
     */
    public function approve($id)
    {
        $nasabah = User::findOrFail($id);
        $nasabah->update([
            'status' => 'aktif'
        ]);

        return redirect('/admin/nasabah')->with('success', 'Akun nasabah berhasil disetujui dan diaktifkan!');
    }

    /**
     * MENGHAPUS DATA NASABAH FROM DATABASE
     */
    public function destroy($id)
    {
        $nasabah = User::findOrFail($id);
        $nasabah->delete();

        return redirect('/admin/nasabah')->with('success', 'Data akun nasabah berhasil dihapus permanen.');
    }
    
    /**
     * UPDATE STATUS NASABAH VIA DROPDOWN DI TABEL UTAMA
     */
    public function updateStatus(Request $request, $id) 
    {
        $request->validate([
            'status' => 'required'
        ]);

        $nasabah = User::findOrFail($id);
        $nasabah->update([
            'status' => $request->status
        ]);

        return redirect('/admin/nasabah')->with('success', 'Status verifikasi nasabah berhasil diperbarui!');
    }

    public function printQr($id)
    {
        $nasabah = User::findOrFail($id);
        
        // Membuka view khusus print
        return view('admin.nasabah.print-qr', compact('nasabah'));
    }
}