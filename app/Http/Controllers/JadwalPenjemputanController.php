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
    /**
 * 🔄 PATCH: KURIR UPDATE STATUS JADWAL JADI PROSES (MULAI JEMPUT)
 */
    public function mulaiJemput($id)
    {
        DB::beginTransaction();
        try {
            // 1. Cari data jadwal penjemputan
            $jadwal = JadwalPenjemputan::find($id);

            if (!$jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data jadwal penjemputan tidak ditemukan.'
                ], 404);
            }

            // 2. Ubah status jadwal dari 'terjadwal' menjadi 'proses'
            $jadwal->status = 'proses';
            $jadwal->save();

            // 3. Pastikan draf di setor_sampahs yang terikat ikut dipastikan berstatus 'proses'
            // (Langkah preventif agar sinkronisasi data tetap terjaga)
            \App\Models\SetorSampah::where('jadwal_id', $id)
                ->where('status', 'proses')
                ->update(['status' => 'proses']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '🚚 Status berhasil diperbarui! Kurir sedang dalam perjalanan menuju lokasi.',
                'data' => $jadwal
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status penjemputan: ' . $e->getMessage()
            ], 500);
        }
    }
}