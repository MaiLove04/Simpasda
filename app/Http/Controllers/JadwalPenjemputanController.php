<?php

namespace App\Http\Controllers;
use App\Models\SetorSampah;

use Illuminate\Support\Facades\DB;
use App\Models\JadwalPenjemputan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalPenjemputanController extends Controller
{
    // ==========================================
    // AMBIL JADWAL KURIR (DIPANGGIL FLUTTER)
    // ==========================================
    public function jadwalKurir($id)
{
    try {

        /*
        |--------------------------------------------------------------------------
        | 1. AMBIL JADWAL DARI ADMIN
        |--------------------------------------------------------------------------
        */

        $jadwalAdmin = JadwalPenjemputan::with([
                'nasabah',
                'kurir',
                'bankSampah'
            ])
            ->where('kurir_id', $id)
            ->whereIn('status', ['terjadwal', 'proses'])
            ->get()
            ->map(function ($item) {

                return [

                    'tipe_tugas' => 'jadwal',

                    'referensi_id' => $item->id,

                    'jadwal_id' => $item->id,

                    'setor_sampah_id' => null,

                    'tanggal' => $item->tanggal_penjemputan,

                    'status' => $item->status,

                    'nasabah' => $item->nasabah,

                    'alamat' => $item->alamat,

                    'catatan' => $item->catatan,

                ];
            });


        /*
        |--------------------------------------------------------------------------
        | 2. AMBIL REQUEST DARI NASABAH
        |--------------------------------------------------------------------------
        */

        $requestNasabah = SetorSampah::with([
                'nasabah',
                'details.jenisSampah'
            ])
            ->where('kurir_id', $id)
            ->whereNull('jadwal_id')
            ->where('status', 'pending')
            ->get()
            ->map(function ($item) {

                return [

                    'tipe_tugas' => 'request',

                    'referensi_id' => $item->id,

                    'jadwal_id' => null,

                    'setor_sampah_id' => $item->id,

                    'tanggal' => $item->created_at,

                    'status' => $item->status,

                    'nasabah' => $item->nasabah,

                    'alamat' => $item->nasabah->alamat ?? '',

                    'catatan' => $item->catatan,

                ];
            });


        /*
        |--------------------------------------------------------------------------
        | 3. GABUNGKAN
        |--------------------------------------------------------------------------
        */

        $semuaTugas = $jadwalAdmin
            ->merge($requestNasabah)
            ->sortBy('tanggal')
            ->values();


        return response()->json([

            'success' => true,

            'message' => 'Daftar tugas berhasil dimuat.',

            'total' => $semuaTugas->count(),

            'data' => $semuaTugas

        ], 200);

    } catch (\Exception $e) {

        return response()->json([

            'success' => false,

            'message' => 'Gagal mengambil daftar tugas.',

            'error' => $e->getMessage(),

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