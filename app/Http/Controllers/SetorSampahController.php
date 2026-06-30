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
            'user_id'     => 'required',
            'grand_total' => 'required',
            'sampah_list' => 'required',
            'foto_sampah' => 'nullable',
            'jadwal_id'   => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Validasi Input: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        DB::beginTransaction();

        try {
            $pathFoto = $this->uploadFoto($request);

            $setor = SetorSampah::find($id) ?? new SetorSampah();
            $setor->user_id = $request->user_id;

            $defaultKurir = User::where('role', 'kurir')->first();
            $setor->kurir_id = $request->kurir_id ?? ($defaultKurir ? $defaultKurir->id : 14);
            $setor->total = $request->grand_total;
            $setor->foto_sampah = $pathFoto ?? $setor->foto_sampah ?? "";
            $setor->catatan = $request->catatan ?? 'Selesai ditimbang oleh kurir lapangan (Jadwal Admin)';
            $setor->status = 'selesai';
            $setor->save();

            $this->simpanDetailSampah($setor->id, $request->sampah_list);
            $this->tambahSaldoNasabah($request->user_id, $request->grand_total);
            $this->catatMutasi($request->user_id, $setor->id, $request->grand_total);

            if ($request->filled('jadwal_id')) {
                JadwalPenjemputan::where('id', $request->jadwal_id)->update(['status' => 'selesai']);
            }

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
            'user_id'     => 'required',
            'grand_total' => 'required',
            'sampah_list' => 'required',
            'foto_sampah' => 'nullable',
            'jadwal_id'   => 'nullable'
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
            $pathFoto = $this->uploadFoto($request);

            $setor->user_id = $request->user_id;
            $defaultKurir = User::where('role', 'kurir')->first();
            $setor->kurir_id = $request->kurir_id ?? ($defaultKurir ? $defaultKurir->id : 14);
            $setor->total = $request->grand_total;

            if ($pathFoto) {
                $setor->foto_sampah = $pathFoto;
            }

            $setor->catatan = $request->catatan ?? 'Berat di-update dan diselesaikan oleh kurir lapangan';
            $setor->status = 'selesai';
            $setor->save();

            $this->simpanDetailSampah($setor->id, $request->sampah_list);
            $this->tambahSaldoNasabah($request->user_id, $request->grand_total);
            $this->catatMutasi($request->user_id, $setor->id, $request->grand_total);

            if ($request->filled('jadwal_id')) {
                JadwalPenjemputan::where('id', $request->jadwal_id)->update(['status' => 'selesai']);
            }

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
     * 📥 =========================================================================
     * 🔥 SINKRONISASI TOTAL DENGAN SCREEN REQUEST FORMULIR NASABAH FLUTTER
     * =========================================================================
     */
    public function requestPenjemputan(Request $request)
    {
        // Melonggarkan validasi array agar fleksibel membaca request JSON murni dari Flutter
        $request->validate([
            'user_id' => 'required',
            'items'   => 'required',
        ]);

        DB::beginTransaction();
        try {
            $nasabah = User::find($request->user_id);
            $kurirAktif = User::where('role', 'kurir')->first();
            $kurirIdDefault = $kurirAktif ? $kurirAktif->id : null; 

            $bankSampah = \App\Models\BankSampah::first();
            $bankId = $bankSampah ? $bankSampah->id : 1;

            // 1. Buat agenda rencana di tabel jadwal_penjemputans
            $jadwal = new JadwalPenjemputan();
            $jadwal->nasabah_id = $request->user_id;
            $jadwal->kurir_id = $kurirIdDefault;
            $jadwal->bank_sampah_id = $bankId;
            $jadwal->alamat = $nasabah->alamat ?? 'Alamat tidak diisi nasabah';
            $jadwal->tanggal_penjemputan = Carbon::today()->format('Y-m-d');
            $jadwal->tanggal = Carbon::today()->format('Y-m-d'); 
            $jadwal->status = 'proses';
            $jadwal->catatan = $request->catatan ?? 'Request jemput lewat aplikasi nasabah';
            $jadwal->save();

            // 2. Buat draf nota gantung di tabel setor_sampahs
            $setor = new SetorSampah();
            $setor->user_id = $request->user_id;
            $setor->kurir_id = $kurirIdDefault;
            $setor->jadwal_id = $jadwal->id;
            $setor->total = 0;
            $setor->foto_sampah = null;
            $setor->catatan = $request->catatan ?? 'Request jemput mandiri nasabah';
            $setor->status = 'proses';
            $setor->save();

            // 3. 🛠️ PERBAIKAN UTAMA: Mengurai data array JSON murni `[1, 2, 3]` hasil kiriman `selectedJenisIds` UI Nasabah
            $items = $request->json('items') ?? $request->items ?? [];
            if (is_string($items)) {
                $items = json_decode($items, true);
            }

            foreach ($items as $item) {
                // Deteksi cerdas: mendukung objek kustom maupun angka ID murni langsung dari item_sampah UI
                $jenisSampahId = is_array($item) ? ($item['jenis_sampah_id'] ?? null) : $item;

                if ($jenisSampahId) {
                    // Ambil harga aktif saat ini dari tabel master jenis sampah agar kalkulasi kurir tidak null/NaN
                    $masterSampah = \App\Models\JenisSampah::find($jenisSampahId);
                    $hargaSaatIni = $masterSampah ? ($masterSampah->harga ?? $masterSampah->harga_beli ?? 2000) : 2000;

                    $detail = new DetailSetorSampah();
                    $detail->setor_sampah_id = $setor->id;
                    $detail->jenis_sampah_id = $jenisSampahId;
                    $detail->berat           = 0; // Inisialisasi awal 0 agar scannable di keranjang kurir
                    $detail->harga_per_kg    = $hargaSaatIni; // Mengunci harga pasaran terkini
                    $detail->total_harga     = 0; 
                    $detail->save();
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => '✅ Request penjemputan & draf manifes kategori sampah berhasil didaftarkan!',
                'data' => [
                    'jadwal' => $jadwal,
                    'setor'  => $setor->load('details')
                ]
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
     * 🔍 AMBIL MANIFES REQUEST NASABAH (AUTOLOAD UNTUK KURIR)
     */
    public function showRequestDetail($nasabah_id)
    {
        try {
            $setorMaster = SetorSampah::where('user_id', $nasabah_id)
                ->where('status', 'proses')
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
                    $hargaBeliTerupdate = $item->harga_per_kg 
                                        ?? $item->jenisSampah->harga_beli
                                        ?? $item->jenisSampah->harga
                                        ?? 2000;

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

    private function uploadFoto(Request $request)
    {
        if ($request->hasFile('foto_sampah')) {
            $file = $request->file('foto_sampah');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/sampah'), $namaFile);
            return 'uploads/sampah/' . $namaFile;
        }
        return null;
    }

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
                $detail->total_harga     = $item['total_item']; 
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