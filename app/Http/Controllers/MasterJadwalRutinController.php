<?php

namespace App\Http\Controllers;

use App\Models\MasterJadwalRutin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class MasterJadwalRutinController extends Controller
{
    // 🔥 METHOD UNTUK GENERATE JADWAL VIA TOMBOL WEB
    public function manualGenerate()
    {
        try {
            Artisan::call('jadwal:generate-harian', ['--today' => true]);
            return redirect()->back()->with('success', 'Jadwal hari ini berhasil disinkronkan ke mobile kurir!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $masterJadwals = MasterJadwalRutin::with(['nasabah', 'kurir'])
            ->whereHas('nasabah', function ($query) {
                $query->where('bank_sampah_id', Auth::user()->bank_sampah_id);
            })
            ->get();
        return view('admin.master_jadwal.index', compact('masterJadwals'));
    }

    public function create()
    {
        $nasabahs = User::where('role', 'nasabah')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->get();

        $kurirs = User::where('role', 'kurir')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->get();

        $hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return view('admin.master_jadwal.create', compact('nasabahs', 'kurirs', 'hariOptions'));
    }

    public function store(Request $request)
    {
        $rules = [
            'nasabah_id' => 'required',
            'kurir_id' => 'required',
            'jam_estimasi' => 'required',
            'tipe_jadwal' => 'required|in:mingguan,interval',
        ];

        // Validasi dinamis berdasarkan tipe jadwal
        if ($request->tipe_jadwal === 'mingguan') {
            $rules['hari_penjemputan'] = 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu';
        } else {
            $rules['interval_hari'] = 'required|integer|min:2';
            $rules['tanggal_mulai'] = 'required|date';
        }

        $request->validate($rules);

        $data = [
            'nasabah_id' => $request->nasabah_id,
            'kurir_id' => $request->kurir_id,
            'jam_estimasi' => $request->jam_estimasi,
            'tipe_jadwal' => $request->tipe_jadwal,
            'is_aktif' => true,
        ];

        if ($request->tipe_jadwal === 'mingguan') {
            $data['hari_penjemputan'] = $request->hari_penjemputan;
            $data['interval_hari'] = null;
            $data['tanggal_mulai'] = null;
        } else {
            $data['hari_penjemputan'] = null;
            $data['interval_hari'] = $request->interval_hari;
            $data['tanggal_mulai'] = $request->tanggal_mulai;
        }

        MasterJadwalRutin::create($data);

        return redirect()->route('master-jadwal.index')->with('success', 'Pola penjemputan rutin berhasil ditambahkan!');
    }

    // Menampilkan Form Edit Pola
    public function edit($id)
    {
        $master = MasterJadwalRutin::whereHas('nasabah', function ($query) {
            $query->where('bank_sampah_id', Auth::user()->bank_sampah_id);
        })->findOrFail($id);

        $nasabahs = User::where('role', 'nasabah')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->get();

        $kurirs = User::where('role', 'kurir')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->get();

        $hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return view('admin.master_jadwal.edit', compact('master', 'nasabahs', 'kurirs', 'hariOptions'));
    }

    // Memproses Pembaruan Pola ke Database
    public function update(Request $request, $id)
    {
        $rules = [
            'nasabah_id' => 'required',
            'kurir_id' => 'required',
            'jam_estimasi' => 'required',
            'tipe_jadwal' => 'required|in:mingguan,interval',
        ];

        if ($request->tipe_jadwal === 'mingguan') {
            $rules['hari_penjemputan'] = 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu';
        } else {
            $rules['interval_hari'] = 'required|integer|min:2';
            $rules['tanggal_mulai'] = 'required|date';
        }

        $request->validate($rules);

        $master = MasterJadwalRutin::whereHas('nasabah', function ($query) {
            $query->where('bank_sampah_id', Auth::user()->bank_sampah_id);
        })->findOrFail($id);

        $data = [
            'nasabah_id' => $request->nasabah_id,
            'kurir_id' => $request->kurir_id,
            'jam_estimasi' => $request->jam_estimasi,
            'tipe_jadwal' => $request->tipe_jadwal,
            'is_aktif' => $request->has('is_aktif') ? true : false,
        ];

        if ($request->tipe_jadwal === 'mingguan') {
            $data['hari_penjemputan'] = $request->hari_penjemputan;
            $data['interval_hari'] = null;
            $data['tanggal_mulai'] = null;
        } else {
            $data['hari_penjemputan'] = null;
            $data['interval_hari'] = $request->interval_hari;
            $data['tanggal_mulai'] = $request->tanggal_mulai;
        }

        $master->update($data);

        return redirect()->route('master-jadwal.index')->with('success', 'Pola penjemputan rutin berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $pola = MasterJadwalRutin::whereHas('nasabah', function ($query) {
            $query->where('bank_sampah_id', Auth::user()->bank_sampah_id);
        })->findOrFail($id);

        $pola->delete();

        return redirect()->route('master-jadwal.index')->with('success', 'Pola penjemputan rutin berhasil dihapus!');
    }

    public function generate(\Illuminate\Http\Request $request)
    {
        // Tulis logika program untuk men-generate jadwal kamu di sini
        
        // Contoh return setelah sukses (silakan sesuaikan):
        return redirect()->back()->with('success', 'Jadwal rutin berhasil di-generate!');
    }


}
