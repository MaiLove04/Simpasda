<?php

namespace App\Http\Controllers;

use App\Traits\SendsPushNotifications;
use App\Models\JadwalPenjemputan;
use Illuminate\Http\Request;

class JadwalPenjemputanController extends Controller
{
    //use SendsPushNotifications;
    use SendsPushNotifications;

    // ==========================================
    // AMBIL JADWAL KURIR (DIPANGGIL FLUTTER)
    // ==========================================
    public function jadwalKurir($id)
    {
        try {
            $jadwal = JadwalPenjemputan::with(['nasabah', 'kurir', 'bankSampah'])
                ->where('kurir_id', $id)
                ->whereNotIn('status', ['selesai', 'batal']) // Ambil semua yang masih aktif
                ->orderBy('tanggal_penjemputan', 'asc')
                ->get();

            return response()->json([
                'success' => true,
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

            // Kirim notifikasi ke Nasabah
            $nasabah = $jadwal->nasabah;
            if ($nasabah && $nasabah->fcm_token) {
                $this->sendPushNotification($nasabah->fcm_token, 'Kurir Dalam Perjalanan', 'Kurir sedang dalam perjalanan untuk menjemput sampah Anda.');
            }

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

    // ==========================================
    // BATALKAN JADWAL (AKSI NASABAH VIA FLUTTER)
    // ==========================================
    public function batalJemput(Request $request, $id)
    {
        try {
            $nasabah = $request->user();
            $jadwal = JadwalPenjemputan::find($id);

            // 1. Validasi: Jadwal tidak ditemukan
            if (!$jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal penjemputan tidak ditemukan.'
                ], 404);
            }

            // 2. Validasi Keamanan: Pastikan nasabah hanya bisa membatalkan jadwal miliknya sendiri
            if ($jadwal->nasabah_id !== $nasabah->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk membatalkan jadwal ini.'
                ], 403);
            }

            // 3. Validasi Logika: Jadwal hanya bisa dibatalkan jika statusnya masih 'terjadwal'
            if ($jadwal->status !== 'terjadwal') {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal ini tidak dapat dibatalkan karena sedang dalam proses atau telah selesai.'
                ], 400);
            }

            // Update status menjadi 'dibatalkan'
            $jadwal->status = 'batal';
            $jadwal->save();

            // Kirim notifikasi ke Kurir bahwa jadwal dibatalkan
            $kurir = $jadwal->kurir;
            if ($kurir && $kurir->fcm_token) {
                $this->sendPushNotification($kurir->fcm_token, 'Jadwal Dibatalkan', "Jadwal penjemputan untuk nasabah {$nasabah->name} telah dibatalkan.");
            }

            return response()->json(['success' => true, 'message' => 'Jadwal penjemputan berhasil dibatalkan.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal membatalkan jadwal: ' . $e->getMessage()], 500);
        }
    }
}
