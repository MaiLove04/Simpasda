<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah;
use App\Models\DetailSetorSampah;
use App\Models\User;
use App\Models\MutasiSaldo;
use App\Models\JadwalPenjemputan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SetorSampahController extends Controller
{
    /**
     * 📋 AMBIL RIWAYAT BERDASARKAN KURIR (UNTUK FLUTTER)
     */
    public function getRiwayatTotal($kurir_id)
    {
        $riwayat = SetorSampah::with(['nasabah', 'details.jenisSampah'])
                    ->where('kurir_id', $kurir_id)
                    ->latest()
                    ->get();

        return response()->json($riwayat, 200);
    }

    /**
     * ⚖️ PATCH 1: KURIR SETOR JADWAL ADMIN (Edit Jenis & Berat Sekaligus)
     */
    public function setorJadwalAdmin(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'user_id'     => 'required|integer',
            'grand_total' => 'required|numeric',
            'sampah_list' => 'required',
            'jadwal_id'   => 'nullable|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Validasi Input: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        DB::beginTransaction();

        try {
            $setor = SetorSampah::firstOrNew(['jadwal_id' => $id]);

            // 🛠️ Urai list sampah terlebih dahulu untuk mengambil data item pertama sebagai fallback database
            $sampahList = json_decode($request->sampah_list, true) ?? [];
            $itemPertama = count($sampahList) > 0 ? $sampahList[0] : null;

            $setor->user_id   = $request->user_id;
            $setor->kurir_id  = $request->kurir_id;
            $setor->total     = $request->grand_total;
            $setor->catatan   = $request->catatan ?? 'Selesai ditimbang oleh kurir lapangan (Jadwal Admin)';
            $setor->status    = 'selesai';

            // 🔥 ISI KOLOM WAJIB DATABASE BERDASARKAN ITEM PERTAMA AGAR TIDAK ERROR 1364
            $setor->jenis_sampah_id = $itemPertama ? $itemPertama['jenis_sampah_id'] : null;
            $setor->berat           = $itemPertama ? $itemPertama['berat'] : 0;
            $setor->harga_per_kg    = $itemPertama ? ($itemPertama['harga_per_kg'] ?? 0) : 0;

            $setor->save(); // UUID otomatis terinjeksi di sini

            $this->simpanDetailSampah($setor->id, $request->sampah_list);
            $this->tambahSaldoNasabah($request->user_id, $request->grand_total);
            $this->catatMutasi($request->user_id, $setor->id, $request->grand_total);

            JadwalPenjemputan::where('id', $id)->update(['status' => 'selesai']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Setoran dari jadwal admin sukses diproses oleh kurir!',
                'data' => $setor->load('details')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses setoran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ⚖️ PATCH 2: KURIR SETOR REQUEST NASABAH (Hanya Update Berat)
     */
    public function setorRequestNasabah(Request $request, $setor_sampah_id)
    {
        $validator = \Validator::make($request->all(), [
            'user_id'     => 'required|integer',
            'grand_total' => 'required|numeric',
            'sampah_list' => 'required',
            'jadwal_id'   => 'nullable|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Validasi Input: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        DB::beginTransaction();

        try {
            $setor = SetorSampah::findOrFail($setor_sampah_id);

            // 🛠️ Urai list sampah terlebih dahulu untuk fallback database
            $sampahList = json_decode($request->sampah_list, true) ?? [];
            $itemPertama = count($sampahList) > 0 ? $sampahList[0] : null;

            $setor->user_id  = $request->user_id;
            $setor->kurir_id = $request->kurir_id;
            $setor->total    = $request->grand_total;
            $setor->catatan  = $request->catatan ?? 'Berat di-update dan diselesaikan oleh kurir lapangan';
            $setor->status   = 'selesai';

            // 🔥 ISI KOLOM WAJIB DATABASE BERDASARKAN ITEM PERTAMA AGAR TIDAK ERROR 1364
            $setor->jenis_sampah_id = $itemPertama ? $itemPertama['jenis_sampah_id'] : $setor->jenis_sampah_id;
            $setor->berat           = $itemPertama ? $itemPertama['berat'] : $setor->berat;
            $setor->harga_per_kg    = $itemPertama ? ($itemPertama['harga_per_kg'] ?? 0) : $setor->harga_per_kg;

            $setor->save();

            $this->simpanDetailSampah($setor->id, $request->sampah_list);
            $this->tambahSaldoNasabah($request->user_id, $request->grand_total);
            $this->catatMutasi($request->user_id, $setor->id, $request->grand_total);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Berat request nasabah berhasil diperbarui dan diselesaikan!',
                'data' => $setor->load('details')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui berat request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📥 CREATE REQUEST PENJEMPUTAN (NASABAH) - DUKUNG LEBIH DARI 1 JENIS SAMPAH
     */
    public function requestPenjemputan(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'items'   => 'required',
        ]);

        // 🛠️ 1. Urai items di awal agar bisa mengintip ID jenis sampah yang pertama di-klik
        $items = $request->json('items') ?? $request->items ?? [];
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        // Ambil elemen pertama dari list pilihan ikon nasabah
        $itemPertama = !empty($items) ? $items[0] : null;
        $jenisSampahIdPertama = is_array($itemPertama) ? ($itemPertama['jenis_sampah_id'] ?? null) : $itemPertama;

        // Validasi pengaman jika array kiriman kosong
        if (!$jenisSampahIdPertama) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses: Kategori sampah yang dipilih tidak valid.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $setor = new SetorSampah();
            $setor->user_id = $request->user_id;
            $setor->kurir_id = null;
            $setor->total = 0;
            $setor->catatan = $request->catatan;
            $setor->status = 'pending';

            // 🔥 FIX: Masukkan ID pertama di sini agar lolos proteksi 'NOT NULL' database MySQL
            $setor->jenis_sampah_id = $jenisSampahIdPertama;
            $setor->berat = 0;
            $setor->harga_per_kg = 0;

            // Masukkan tanggal_penjemputan otomatis jika dikirimkan dari validasi jam kerja Flutter
            if ($request->has('tanggal_penjemputan')) {
                $setor->tanggal_penjemputan = $request->tanggal_penjemputan;
            }

            $setor->save();

            // 🛠️ 2. Simpan semua data jenis sampah ke tabel detail pecahan (Mendukung > 1 Jenis)
            foreach ($items as $item) {
                $jenisSampahId = is_array($item) ? ($item['jenis_sampah_id'] ?? null) : $item;

                if ($jenisSampahId) {
                    $masterSampah = \App\Models\JenisSampah::find($jenisSampahId);
                    $hargaSaatIni = $masterSampah ? (int) $masterSampah->harga_per_kg : 0;

                    $detail = new DetailSetorSampah();
                    $detail->setor_sampah_id = $setor->id;
                    $detail->jenis_sampah_id = $jenisSampahId;
                    $detail->berat           = 0;
                    $detail->harga_per_kg    = $hargaSaatIni;
                    $detail->subtotal        = 0;
                    $detail->save();
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Request penjemputan berhasil dikirim.',
                'data' => $setor->load('details'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses di server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 AMBIL DATA COUNTER REAL-TIME UNTUK DASHBOARD KURIR
     */
    public function getDashboardKurir($kurir_id)
    {
        try {
            $totalTransaksi = SetorSampah::where('kurir_id', $kurir_id)->count();
            $setorIds = SetorSampah::where('kurir_id', $kurir_id)->pluck('id');
            $totalKgSampah = DetailSetorSampah::whereIn('setor_sampah_id', $setorIds)->sum('berat');
            $totalUangDiproses = SetorSampah::where('kurir_id', $kurir_id)->sum('total');

            return response()->json([
                'success'             => true,
                'kurir_id'            => $kurir_id,
                'total_transaksi'     => $totalTransaksi,
                'total_kg_sampah'     => round($totalKgSampah, 2),
                'total_uang_diproses' => (int) $totalUangDiproses
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat ringkasan dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔍 AMBIL MANIFES REQUEST NASABAH (AUTOLOAD UNTUK KURIR)
     */
    public function showRequestDetail($nasabah_id)
    {
        try {
            $setorMaster = SetorSampah::where('user_id',$nasabah_id)
            ->whereNull('jadwal_id')
            ->where('status','pending')
            ->latest()
            ->first();

            if (!$setorMaster) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada manifes request aktif untuk nasabah ini.'
                ], 404);
            }

            $details = DetailSetorSampah::where('setor_sampah_id', $setorMaster->id)
                ->with('jenisSampah')
                ->get();

            $formAutoload = [];
            foreach ($details as $item) {
                if ($item->jenisSampah) {
                    $hargaBeliTerupdate = $item->harga_per_kg ?? $item->jenisSampah->harga_per_kg ?? 0;

                    $formAutoload[] = [
                        'jenis_sampah_id' => $item->jenis_sampah_id,
                        'nama_sampah'     => $item->jenisSampah->nama,
                        'harga_per_kg'    => (int) $hargaBeliTerupdate,
                        'berat'           => 0,
                        'total_item'      => 0
                    ];
                }
            }

            return response()->json([
                'success'         => true,
                'setor_sampah_id' => $setorMaster->id,
                'items'           => $formAutoload
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal meload data request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * =========================================================================
     * 🛠️ INTERNAL HELPER PRIVATE METHODS
     * =========================================================================
     */

    private function simpanDetailSampah($setorId, $sampahListJson)
    {
        $sampahList = json_decode($sampahListJson, true);
        if (is_array($sampahList)) {
            DetailSetorSampah::where('setor_sampah_id', $setorId)->delete();

            foreach ($sampahList as $item) {
                $detail = new DetailSetorSampah();
                $detail->setor_sampah_id = $setorId;
                $detail->jenis_sampah_id = $item['jenis_sampah_id'];
                $detail->berat           = $item['berat'];
                $detail->harga_per_kg    = $item['harga_per_kg'];
                $detail->subtotal        = $item['total_item']; 
                $detail->save();
            }
        }
    }

    private function tambahSaldoNasabah($userId, $grandTotal)
    {
        $nasabah = User::find($userId);
        if ($nasabah) {
            $nominalMasuk = (int) $grandTotal;
            $nasabah->saldo = $nasabah->saldo + $nominalMasuk;
            $nasabah->save();
        }
    }

    private function catatMutasi($userId, $referensiId, $grandTotal)
    {
        $mutasi = new MutasiSaldo();
        $mutasi->user_id         = $userId;
        $mutasi->jenis_transaksi = 'masuk';
        $mutasi->sumber          = 'setor_sampah';
        $mutasi->referensi_id    = $referensiId;
        $mutasi->nominal         = (int) $grandTotal;
        $mutasi->status          = 'success';
        $mutasi->keterangan      = 'Uang masuk dari penimbangan sampah lapangan';
        $mutasi->save();
    }
}