<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah;
use App\Models\DetailSetorSampah;
use App\Models\User;
use App\Models\MutasiSaldo;
use App\Models\JadwalPenjemputan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
   /**
     * ⚖️ PATCH 1: KURIR SETOR JADWAL ADMIN (Edit Jenis & Berat Sekaligus)
     */
    public function setorJadwalAdmin(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
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
            // 🔥 PEMBERSIH & FIX UUID: Pastikan ID bersih dari karakter kurung kurawal jika ada
            $cleanId = trim($id, '{} ');

            // Cari berdasarkan jadwal_id, jika tidak ada buat instansiasi baru
            $setor = SetorSampah::where('jadwal_id', $cleanId)->first();
            
            if (!$setor) {
                $setor = new SetorSampah();
                $setor->jadwal_id = $cleanId;
            }

            $sampahList = json_decode($request->sampah_list, true) ?? [];
            $itemPertama = count($sampahList) > 0 ? $sampahList[0] : null;

            $setor->user_id   = $request->user_id;
            $setor->kurir_id  = $request->kurir_id;
            $setor->total     = $request->grand_total;
            $setor->catatan   = $request->catatan ?? 'Selesai ditimbang oleh kurir lapangan (Jadwal Admin)';
            $setor->status    = 'selesai';

            $setor->jenis_sampah_id = $itemPertama ? $itemPertama['jenis_sampah_id'] : null;
            $setor->berat           = $itemPertama ? $itemPertama['berat'] : 0;
            $setor->harga_per_kg    = $itemPertama ? ($itemPertama['harga_per_kg'] ?? 0) : 0;

            $setor->save(); 

            $this->simpanDetailSampah($setor->id, $request->sampah_list);
            $this->tambahSaldoNasabah($request->user_id, $request->grand_total);
            $this->catatMutasi($request->user_id, $setor->id, $request->grand_total);

            // 🔍 AMBIL DATA JADWAL HARIAN UNTUK ESTIMASI TANGGAL KELUARAN
            // 🔍 AMBIL DATA JADWAL HARIAN UNTUK ESTIMASI TANGGAL KELUARAN
            $jadwalHarian = JadwalPenjemputan::where('id', $cleanId)->first();
            if ($jadwalHarian) {
                $jadwalHarian->update(['status' => 'selesai']);
            }

            // 🔥 HITUNG MURNI HARI INI (5 Juli 2026) + INTERVAL HARI
            $masterJadwal = \App\Models\MasterJadwalRutin::where('nasabah_id', (int)$request->user_id)->first();
            
            if ($masterJadwal) {
                $interval = (int)($masterJadwal->interval_hari ?? 2);
                
                if ($masterJadwal->tipe_jadwal === 'interval') {
                    // Murni Hari ini + interval hari (5 Juli + 2 = 7 Juli 2026)
                    $nextDate = Carbon::today()->addDays($interval)->toDateString();
                } else {
                    $nextDate = Carbon::today()->addDays(7)->toDateString();
                }

                // 🔨 PAKSA PAKAI SQL MENTAH (Bypass Cache, Properti Model, dan Aturan Framework)
                DB::statement("UPDATE master_jadwal_rutins SET tanggal_penjemputan_berikutnya = ? WHERE nasabah_id = ?", [
                    $nextDate, 
                    (int)$request->user_id
                ]);
            }

            // 7. TAMBAH NOTIFIKASI
            // Notifikasi untuk Nasabah
            $totalBerat = 0;
            foreach ($sampahList as $item) {
                $totalBerat += $item['berat'] ?? 0;
            }
            DB::table('notifikasis')->insert([
                'user_id' => $request->user_id,
                'judul' => 'Sampah Selesai Ditimbang',
                'pesan' => 'Sampah Anda telah selesai ditimbang dengan berat total ' . $totalBerat . ' Kg. Saldo Anda bertambah sebesar Rp ' . number_format($request->grand_total, 0, ',', '.') . '.',
                'type' => 'setoran',
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Notifikasi untuk Kurir
            DB::table('notifikasis')->insert([
                'user_id' => $setor->kurir_id,
                'judul' => 'Setoran Sampah Sukses',
                'pesan' => 'Berhasil memproses setoran sampah untuk nasabah ' . ($nasabah->name ?? 'Nasabah') . ' dengan total Rp ' . number_format($request->grand_total, 0, ',', '.') . '.',
                'type' => 'setoran',
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

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
        $validator = Validator::make($request->all(), [
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

            // TAMBAH NOTIFIKASI
            // Notifikasi untuk Nasabah
            $totalBerat = 0;
            foreach ($sampahList as $item) {
                $totalBerat += $item['berat'] ?? 0;
            }
            $nasabah = User::find($request->user_id);
            DB::table('notifikasis')->insert([
                'user_id' => $request->user_id,
                'judul' => 'Sampah Selesai Ditimbang',
                'pesan' => 'Sampah Anda telah selesai ditimbang dengan berat total ' . $totalBerat . ' Kg. Saldo Anda bertambah sebesar Rp ' . number_format($request->grand_total, 0, ',', '.') . '.',
                'type' => 'setoran',
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Notifikasi untuk Kurir
            DB::table('notifikasis')->insert([
                'user_id' => $request->kurir_id,
                'judul' => 'Setoran Sampah Sukses',
                'pesan' => 'Berhasil memproses setoran sampah untuk nasabah ' . ($nasabah->name ?? 'Nasabah') . ' dengan total Rp ' . number_format($request->grand_total, 0, ',', '.') . '.',
                'type' => 'setoran',
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

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

    
    public function requestPenjemputan(Request $request)
    {
        // 1. Validasi input basic
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'items'   => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Validasi: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        // 🔒 PENGUNCI JADWAL RUTIN: Tetap mendeteksi hari ini berdasarkan tanggal server
        // (Fungsi cekJadwalHariIni tetap berjalan normal di latar belakang memakai tanggal hari ini)
        if ($this->cekJadwalHariIni($request->user_id, Carbon::today()->toDateString())) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, Anda tidak dapat melakukan request mandiri. Hari ini rumah Anda sudah masuk ke dalam daftar plot jadwal penjemputan rutin/petugas. Mohon tunggu kurir datang ke lokasi Anda.'
            ], 422);
        }

        $items = $request->input('items', []);
        if (is_string($items)) {
            $items = json_decode($items, true) ?? [];
        }

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori sampah belum dipilih.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $itemPertama = $items[0];

            $setor = new SetorSampah();
            $setor->user_id = $request->user_id;
            $setor->catatan = $request->catatan ?? 'Permintaan penjemputan mandiri oleh nasabah';
            $setor->status  = 'pending';
            
            // ❌ HAPUS TOTAL: Tidak ada lagi assignment ke tanggal_penjemputan di sini!
            
            // Isi kolom fallback bawaan database lama kamu
            $setor->jenis_sampah_id = $itemPertama['jenis_sampah_id'] ?? null;
            $setor->berat           = 0;
            $setor->harga_per_kg    = 0;
            $setor->total           = 0;
            $setor->save(); // 💾 Aman dikirim ke MySQL tanpa memicu error 1054

            // Loop untuk simpan detail item sampah ke detail_setor_sampahs
            foreach ($items as $item) {
                $detail = new DetailSetorSampah();
                $detail->setor_sampah_id = $setor->id;
                $detail->jenis_sampah_id = $item['jenis_sampah_id'];
                $detail->berat           = 0;
                $detail->harga_per_kg    = 0;
                $detail->subtotal        = 0;
                $detail->save();
            }

            // TAMBAH NOTIFIKASI
            // Notifikasi untuk Nasabah
            $nasabah = User::find($request->user_id);
            DB::table('notifikasis')->insert([
                'user_id' => $request->user_id,
                'judul' => 'Permintaan Penjemputan Terkirim',
                'pesan' => 'Permintaan penjemputan sampah Anda telah berhasil dikirim. Menunggu kurir mengambil tugas.',
                'type' => 'penjemputan',
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Notifikasi untuk Semua Kurir
            $couriers = User::where('role', 'kurir')->get();
            foreach ($couriers as $courier) {
                DB::table('notifikasis')->insert([
                    'user_id' => $courier->id,
                    'judul' => 'Tugas Penjemputan Baru',
                    'pesan' => 'Ada request penjemputan sampah baru dari nasabah ' . ($nasabah->name ?? 'Nasabah') . ' di ' . ($nasabah->alamat ?? 'alamat nasabah') . '.',
                    'type' => 'penjemputan',
                    'is_read' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Permintaan penjemputan sampah berhasil dibuat!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses ke database: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * 🛠️ VALIDASI APAKAH HARI INI NASABAH MEMILIKI JADWAL RUTIN / ADMIN
     */
    private function cekJadwalHariIni($userId, $tanggalTarget)
    {
        $date = Carbon::parse($tanggalTarget)->toDateString();

        // 1. Cek apakah sudah digenerate di JadwalPenjemputan harian (Status: terjadwal/proses)
        $adaJadwalHarian = \App\Models\JadwalPenjemputan::where('nasabah_id', $userId)
            ->whereDate('tanggal_penjemputan', $date)
            ->whereIn('status', ['terjadwal', 'proses'])
            ->exists();

        if ($adaJadwalHarian) {
            return true; 
        }

        // 2. Cek langsung ke Master Pola: Apakah tanggal_penjemputan_berikutnya cocok dengan hari ini?
        $adaMasterHariIni = \App\Models\MasterJadwalRutin::where('nasabah_id', $userId)
            ->where('is_aktif', true)
            ->whereDate('tanggal_penjemputan_berikutnya', $date)
            ->exists();

        if ($adaMasterHariIni) {
            return true;
        }

        return false;
    }

    // /**
    //  * 🛠️ VALIDASI APAKAH HARI INI NASABAH MEMILIKI JADWAL RUTIN / ADMIN
    //  */
    // private function cekJadwalHariIni($userId, $tanggalTarget)
    // {
    //     $date = Carbon::parse($tanggalTarget);
    //     $hariIni = $date->locale('id')->dayName; // Mengambil 'Senin', 'Selasa', dst.

    //     // Kondisi A: Cek apakah sudah digenerate di tabel JadwalPenjemputan harian (Aktif/Proses)
    //     $adaJadwalHarian = \App\Models\JadwalPenjemputan::where('nasabah_id', $userId)
    //         ->whereDate('tanggal_penjemputan', $date->toDateString())
    //         ->whereIn('status', ['terjadwal', 'proses'])
    //         ->exists();

    //     if ($adaJadwalHarian) {
    //         return true; 
    //     }

    //     // Kondisi B: Antisipasi jika Admin belum menekan tombol Force Sync (Cek langsung pola Master)
    //     $masterJadwals = \App\Models\MasterJadwalRutin::where('nasabah_id', $userId)
    //         ->where('is_aktif', true)
    //         ->get();

    //     foreach ($masterJadwals as $master) {
    //         // 1. Cek jika polanya Mingguan
    //         if ($master->tipe_jadwal === 'mingguan' && $master->hari_penjemputan === $hariIni) {
    //             return true;
    //         }

    //         // 2. Cek jika polanya Interval Hari
    //         if ($master->tipe_jadwal === 'interval' && $master->tanggal_mulai) {
    //             $tanggalMulai = Carbon::parse($master->tanggal_mulai)->startOfDay();
    //             $tanggalSekarang = $date->copy()->startOfDay();

    //             if ($tanggalSekarang->greaterThanOrEqualTo($tanggalMulai)) {
    //                 $selisihHari = $tanggalSekarang->diffInDays($tanggalMulai);
    //                 // Jika selisih hari habis dibagi angka interval, berarti hari ini jadwalnya
    //                 if ($selisihHari % $master->interval_hari === 0) {
    //                     return true;
    //                 }
    //             }
    //         }
    //     }

    //     return false;
    // }
    

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
            // 1. Cari data induk transaksi request milik nasabah yang statusnya 'pending'
            $setorMaster = SetorSampah::where('user_id', $nasabah_id)
                ->whereNull('jadwal_id')
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$setorMaster) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada manifes request aktif untuk nasabah ini.',
                    'setor_sampah_id' => '',
                    'items' => []
                ], 200);
            }

            // 2. Ambil detail item sampah beserta relasi jenis sampahnya
            $details = DetailSetorSampah::where('setor_sampah_id', $setorMaster->id)
                ->with('jenisSampah')
                ->get();

            $formAutoload = [];
            foreach ($details as $item) {
                if ($item->jenisSampah) {
                    // Ambil harga terupdate dari master jenis sampah jika kolom di detail kosong
                    $hargaBeli = $item->harga_per_kg ?? $item->jenisSampah->harga_per_kg ?? 0;

                    $formAutoload[] = [
                        'jenis_sampah_id' => (int) $item->jenis_sampah_id,
                        'nama_sampah'     => (string) $item->jenisSampah->nama,
                        'harga_per_kg'    => (int) $hargaBeli,
                        'berat'           => 0.0, // Dipaksa double agar klop dengan kodingan Flutter
                        'total_item'      => 0
                    ];
                }
            }

            // 🚀 Return data dengan struktur tingkat utama (items) sesuai ekspektasi Flutter
            return response()->json([
                'success'         => true,
                'setor_sampah_id' => $setorMaster->id,
                'items'           => $formAutoload
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal meload data request: ' . $e->getMessage(),
                'setor_sampah_id' => '',
                'items' => []
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