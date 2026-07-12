<?php

namespace App\Http\Controllers;

use App\Models\JadwalPenjemputan;
use Illuminate\Http\Request;

class JadwalPenjemputanController extends Controller
{
    // ==========================================
    // AMBIL JADWAL KURIR (DIPANGGIL FLUTTER)
    // ==========================================
    public function jadwalKurir($id)
    {
        try {
            $jadwal = JadwalPenjemputan::with([
                'nasabah',
                'kurir',
                'bankSampah'
            ])
            ->where('kurir_id', $id)
            ->where('status', '!=', 'selesai') // Hanya tampilkan yang belum selesai
            ->latest()
            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data jadwal berhasil diambil',
                'data'    => $jadwal
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jadwal',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ==========================================
    // SIMPAN JADWAL PENJEMPUTAN (DIPANGGIL API/ADMIN)
    // ==========================================
    public function store(Request $request)
    {
        $request->validate([
            'nasabah_id'          => 'required|exists:users,id',
            'kurir_id'            => 'required|exists:users,id',
            'tanggal_penjemputan' => 'required|date',
            'alamat'              => 'required|string',
            'catatan'             => 'nullable|string',
        ]);

        try {
            $admin = auth()->user();

            $jadwal = JadwalPenjemputan::create([
                'bank_sampah_id'      => $admin->bank_sampah_id,
                'nasabah_id'          => $request->nasabah_id,
                'kurir_id'            => $request->kurir_id,
                'tanggal_penjemputan' => $request->tanggal_penjemputan,
                'alamat'              => $request->alamat,
                'catatan'             => $request->catatan,
                'status'              => 'terjadwal',
            ]);

            // TAMBAH NOTIFIKASI
            // Notifikasi untuk Kurir
            \App\Models\Notifikasi::create([
                'user_id' => $request->kurir_id,
                'judul' => 'Tugas Penjemputan Baru',
                'pesan' => 'Jadwal penjemputan sampah baru telah ditugaskan kepada Anda untuk tanggal ' . $request->tanggal_penjemputan . ' di ' . $request->alamat . '.',
                'type' => 'penjemputan',
                'is_read' => false,
            ]);

            // Notifikasi untuk Nasabah
            \App\Models\Notifikasi::create([
                'user_id' => $request->nasabah_id,
                'judul' => 'Jadwal Penjemputan Dibuat',
                'pesan' => 'Jadwal penjemputan sampah Anda telah diatur untuk tanggal ' . $request->tanggal_penjemputan . '.',
                'type' => 'penjemputan',
                'is_read' => false,
            ]);

            $jadwal->load(['nasabah', 'kurir', 'bankSampah']);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dibuat',
                'data'    => $jadwal,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat jadwal',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ==========================================
    // UPDATE STATUS JADWAL JADI PROSES (AKSI KURIR)
    // ==========================================
    public function mulaiJemput($id)
    {
        try {
            $jadwal = JadwalPenjemputan::find($id);

            if (!$jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal tidak ditemukan'
                ], 404);
            }

            // Ubah status menjadi proses penjemputan
            $jadwal->status = 'proses';
            $jadwal->save();

            // TAMBAH NOTIFIKASI
            // Notifikasi untuk Nasabah
            \App\Models\Notifikasi::create([
                'user_id' => $jadwal->nasabah_id,
                'judul' => 'Kurir Sedang Menuju Lokasi',
                'pesan' => 'Kurir sedang dalam perjalanan menuju lokasi Anda untuk menjemput sampah.',
                'type' => 'penjemputan',
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diperbarui menjadi proses',
                'data'    => $jadwal
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status penjemputan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ==========================================
    // AMBIL JADWAL AKTIF NASABAH (DIPANGGIL FLUTTER)
    // ==========================================
    public function jadwalNasabah($id)
    {
        try {
            $jadwal = \App\Models\JadwalPenjemputan::with(['kurir'])
                ->where('nasabah_id', $id)
                ->where('status', '!=', 'selesai')
                ->latest()
                ->first();

            return response()->json([
                'success' => true,
                'data'    => $jadwal
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
