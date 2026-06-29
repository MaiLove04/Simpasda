<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\SendsPushNotifications;
use App\Models\JadwalPenjemputan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalWebController extends Controller
{
    use SendsPushNotifications;

    public function index()
    {
        // Mengambil jadwal yang hanya dimiliki oleh bank sampah milik admin yang login
        $jadwals = JadwalPenjemputan::with(['nasabah', 'kurir'])
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->latest()
            ->get();

        return view('admin.jadwal.index', compact('jadwals'));
    }

    public function create()
    {
        // Mengambil nasabah & kurir yang satu bank sampah dengan admin
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
        // Validasi diperketat untuk memastikan ID benar-benar valid
        $request->validate([
            'nasabah_id'          => 'required|exists:users,id',
            'kurir_id'            => 'required|exists:users,id',
            'tanggal_penjemputan' => 'required|date',
            'alamat'              => 'required|string',
            'catatan'             => 'nullable|string',
        ]);

        $jadwal = JadwalPenjemputan::create([
            'bank_sampah_id'      => Auth::user()->bank_sampah_id,
            'nasabah_id'          => $request->nasabah_id,
            'kurir_id'            => $request->kurir_id,
            'tanggal_penjemputan' => $request->tanggal_penjemputan,
            'alamat'              => $request->alamat,
            'catatan'             => $request->catatan,
            'status'              => 'terjadwal',
        ]);

        // Kirim notifikasi ke Nasabah dan Kurir
        $nasabah = User::find($request->nasabah_id);
        $kurir = User::find($request->kurir_id);

        if ($nasabah && $nasabah->fcm_token) {
            $this->sendPushNotification($nasabah->fcm_token, 'Jadwal Penjemputan Baru', 'Anda memiliki jadwal penjemputan baru yang telah dibuat oleh admin.');
        }

        if ($kurir && $kurir->fcm_token) {
            $this->sendPushNotification($kurir->fcm_token, 'Tugas Penjemputan Baru', 'Anda mendapatkan tugas penjemputan baru dari admin.');
        }


        return redirect('/admin/jadwal')->with('success', 'Jadwal penjemputan berhasil dibuat!');
    }

    public function jadwalKurir($id)
    {
        // Memastikan kurir yang login hanya bisa melihat jadwal miliknya sendiri
        // Atau jika diakses admin, admin hanya bisa melihat jadwal kurir di bank sampahnya
        $userLogedIn = Auth::user();

        $query = JadwalPenjemputan::with(['nasabah', 'kurir'])->where('kurir_id', $id);

        if ($userLogedIn->role === 'admin') {
            $query->where('bank_sampah_id', $userLogedIn->bank_sampah_id);
        }

        $jadwal = $query->get();

        return response()->json([
            'success' => true,
            'data'    => $jadwal
        ]);
    }

    public function mulaiJemput($id)
    {
        $userLogedIn = Auth::user();
        
        $query = JadwalPenjemputan::where('id', $id);

        // Jika yang mengakses adalah kurir, amankan agar dia hanya bisa memulai jadwal miliknya sendiri
        if ($userLogedIn->role === 'kurir') {
            $query->where('kurir_id', $userLogedIn->id);
        } else {
            // Jika admin yang klik via web, batasi berdasarkan bank sampah admin
            $query->where('bank_sampah_id', $userLogedIn->bank_sampah_id);
        }

        $jadwal = $query->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan atau Anda tidak memiliki akses.'
            ], 404);
        }

        // Ubah status menjadi proses
        $jadwal->status = 'proses';
        $jadwal->save();

        // Kirim notifikasi ke Nasabah
        $nasabah = $jadwal->nasabah;
        if ($nasabah && $nasabah->fcm_token) {
            $this->sendPushNotification($nasabah->fcm_token, 'Kurir Dalam Perjalanan', 'Kurir sedang dalam perjalanan untuk menjemput sampah Anda.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui menjadi proses',
            'data'    => $jadwal
        ]);
    }

    public function edit(JadwalPenjemputan $jadwal)
    {
        // Pastikan jadwal yang diakses adalah milik bank sampah admin yang login
        if ($jadwal->bank_sampah_id !== Auth::user()->bank_sampah_id) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini.');
        }

        // Ambil data nasabah & kurir untuk pilihan di dropdown form
        $nasabahs = User::where('role', 'nasabah')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->get();

        $kurirs = User::where('role', 'kurir')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->get();

        return view('admin.jadwal.edit', compact('jadwal', 'nasabahs', 'kurirs'));
    }

    public function update(Request $request, JadwalPenjemputan $jadwal)
    {
        // 1. Pastikan hak akses aman
        if ($jadwal->bank_sampah_id !== Auth::user()->bank_sampah_id) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini.');
        }

        // 2. Validasi data form edit
        $request->validate([
            'nasabah_id'          => 'required|exists:users,id',
            'kurir_id'            => 'required|exists:users,id',
            'tanggal_penjemputan' => 'required|date',
            'alamat'              => 'required|string',
            'status'              => 'required|in:terjadwal,proses,selesai,batal',
            'catatan'             => 'nullable|string',
        ]);

        // 3. Update datanya di database
        $jadwal->update([
            'nasabah_id'          => $request->nasabah_id,
            'kurir_id'            => $request->kurir_id,
            'tanggal_penjemputan' => $request->tanggal_penjemputan,
            'alamat'              => $request->alamat,
            'status'              => $request->status,
            'catatan'             => $request->catatan,
        ]);

        // 4. Redirect ke halaman utama dengan pesan sukses
        return redirect('/admin/jadwal')->with('success', 'Jadwal penjemputan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        // Pengaman ekstra: memastikan admin tidak menghapus jadwal milik bank sampah lain
        $jadwal = JadwalPenjemputan::where('id', $id)
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->firstOrFail();
            
        $jadwal->delete();

        return back()->with('success', 'Jadwal penjemputan berhasil dihapus!');
    }
}