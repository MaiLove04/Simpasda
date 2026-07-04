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
        | 1. AMBIL JADWAL DARI ADMIN (terjadwal + proses)
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

                    'referensi_id' => $item->id ?? null,

                    'jadwal_id' => $item->id ?? null,

                    'setor_sampah_id' => null,

                    'tanggal' => $item->tanggal_penjemputan ?? null,

                    'status' => $item->status ?? null,

                    'nasabah' => $item->nasabah ?? null,

                    'alamat' => $item->alamat ?? null,

                    'catatan' => $item->catatan ?? null,

                ];
            });


        /*
        |--------------------------------------------------------------------------
        | 2. AMBIL REQUEST DARI NASABAH
        | Request masuk dengan kurir_id = NULL. Tampil ke semua kurir di bank
        | sampah yang sama. Kurir pertama yang scan QR nasabah akan mengambilnya.
        |--------------------------------------------------------------------------
        */

        $kurir = \App\Models\User::find($id);
        $bankSampahId = $kurir?->bank_sampah_id;

        $requestNasabah = SetorSampah::with([
                'nasabah',
                'details.jenisSampah'
            ])
            ->whereNull('kurir_id')
            ->whereNull('jadwal_id')
            ->where('status', 'pending')
            ->when($bankSampahId, function ($q) use ($bankSampahId) {
                // Batasi ke nasabah yang satu bank sampah dengan kurir ini
                $q->whereHas('nasabah', function ($q2) use ($bankSampahId) {
                    $q2->where('bank_sampah_id', $bankSampahId);
                });
            })
            ->get()
            ->map(function ($item) {

                return [

                    'tipe_tugas' => 'request',

                    'referensi_id' => $item->id ?? null,

                    'jadwal_id' => null,

                    'setor_sampah_id' => $item->id ?? null,

                    'tanggal' => $item->created_at ?? null,

                    'status' => $item->status ?? null,

                    'nasabah' => $item->nasabah ?? null,

                    'alamat' => $item->nasabah->alamat ?? null,

                    'catatan' => $item->catatan ?? null,

                ];
            });


        /*
        |--------------------------------------------------------------------------
        | 3. GABUNGKAN: Jadwal Admin + Request Nasabah, urutkan by tanggal
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

    /**
     * 🔄 PATCH: KURIR MULAI PENJEMPUTAN (status → proses)
     */
    public function mulaiJemput($id)
    {
        DB::beginTransaction();
        try {
            $jadwal = JadwalPenjemputan::find($id);

            if (!$jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data jadwal penjemputan tidak ditemukan.'
                ], 404);
            }

            $jadwal->status = 'proses';
            $jadwal->save();

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

    /**
     * 🔄 PATCH: KURIR BATALKAN JADWAL PENJEMPUTAN (status → dibatalkan)
     */
    public function batalJemput($id)
    {
        DB::beginTransaction();
        try {
            $jadwal = JadwalPenjemputan::find($id);

            if (!$jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data jadwal penjemputan tidak ditemukan.'
                ], 404);
            }

            if ($jadwal->status === 'selesai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal yang sudah selesai tidak dapat dibatalkan.'
                ], 422);
            }

            $jadwal->status = 'dibatalkan';
            $jadwal->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal penjemputan berhasil dibatalkan.',
                'data' => $jadwal
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan jadwal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * =========================================================================
     * 📅 JADWAL NASABAH — Dipanggil Flutter halaman nasabah
     * Mengembalikan:
     *   - Jadwal mendatang (status: terjadwal, proses)
     *   - Riwayat selesai (setor_sampahs status: selesai)
     * =========================================================================
     */
    public function jadwalNasabah($id)
    {
        try {
            // 1. Jadwal mendatang dari admin (terjadwal & proses)
            $jadwalMendatang = JadwalPenjemputan::with(['kurir', 'bankSampah'])
                ->where('nasabah_id', $id)
                ->whereIn('status', ['terjadwal', 'proses'])
                ->orderBy('tanggal_penjemputan', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tipe'              => 'jadwal',
                        'id'                => $item->id,
                        'tanggal'           => $item->tanggal_penjemputan,
                        'tanggal_formatted' => Carbon::parse($item->tanggal_penjemputan)->locale('id')->isoFormat('dddd, D MMMM Y'),
                        'jam'               => $item->tanggal_penjemputan
                            ? Carbon::parse($item->tanggal_penjemputan)->format('H:i') . ' WIB'
                            : null,
                        'status'            => $item->status,
                        'kurir'             => $item->kurir ? [
                            'nama' => $item->kurir->name,
                            'foto' => $item->kurir->foto,
                            'no_hp'=> $item->kurir->no_hp,
                        ] : null,
                        'catatan'           => $item->catatan,
                        'alamat'            => $item->alamat,
                    ];
                });

            // 2. Request nasabah yang masih pending (belum diambil kurir)
            $requestPending = SetorSampah::with(['details.jenisSampah'])
                ->where('user_id', $id)
                ->whereNull('jadwal_id')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tipe'              => 'request',
                        'id'                => $item->id,
                        'tanggal'           => $item->created_at,
                        'tanggal_formatted' => Carbon::parse($item->created_at)->locale('id')->isoFormat('dddd, D MMMM Y'),
                        'jam'               => Carbon::parse($item->created_at)->format('H:i') . ' WIB',
                        'status'            => $item->status,
                        'kurir'             => null, // Belum ada kurir
                        'catatan'           => $item->catatan,
                        'alamat'            => null,
                        'items_sampah'      => $item->details->map(fn($d) => [
                            'nama'       => $d->jenisSampah->nama ?? '-',
                            'harga_per_kg' => (int) $d->harga_per_kg,
                        ]),
                    ];
                });

            // 3. Riwayat selesai (setor_sampahs status selesai, 10 terakhir)
            $riwayat = SetorSampah::with(['details.jenisSampah', 'kurir'])
                ->where('user_id', $id)
                ->where('status', 'selesai')
                ->orderBy('updated_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'tipe'              => 'selesai',
                        'id'                => $item->id,
                        'tanggal'           => $item->updated_at,
                        'tanggal_formatted' => Carbon::parse($item->updated_at)->locale('id')->isoFormat('dddd, D MMMM Y'),
                        'jam'               => Carbon::parse($item->updated_at)->format('H:i') . ' WIB',
                        'status'            => $item->status,
                        'total'             => (int) $item->total,
                        'kurir'             => $item->kurir ? [
                            'nama' => $item->kurir->name,
                            'foto' => $item->kurir->foto,
                        ] : null,
                        'catatan'           => $item->catatan,
                        'foto_sampah'       => $item->foto_sampah,
                        'items_sampah'      => $item->details->map(fn($d) => [
                            'nama'       => $d->jenisSampah->nama ?? '-',
                            'berat'      => (float) $d->berat,
                            'harga_per_kg' => (int) $d->harga_per_kg,
                            'subtotal'   => (int) $d->subtotal,
                        ]),
                    ];
                });

            return response()->json([
                'success'          => true,
                'jadwal_mendatang' => $jadwalMendatang,
                'request_pending'  => $requestPending,
                'riwayat'          => $riwayat,
                'total_mendatang'  => $jadwalMendatang->count() + $requestPending->count(),
                'total_riwayat'    => $riwayat->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat jadwal nasabah: ' . $e->getMessage(),
            ], 500);
        }
    }
}
