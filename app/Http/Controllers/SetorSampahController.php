<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah;
use App\Models\DetailSetorSampah; 
use App\Models\User; // Ditambahkan agar bisa memanggil model User Nasabah
use App\Models\MutasiSaldo; // Ditambahkan agar bisa memanggil model MutasiSaldo
use Illuminate\Support\Facades\DB;

class SetorSampahController extends Controller
{
    /**
     * AMBIL RIWAYAT BERDASARKAN KURIR (UNTUK FLUTTER)
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
     * ✅ FIX FINAL API MULTI-SETOR: AUTOMATISASI SALDO & MUTASI (BEBAS TYPO)
     */
    public function store(Request $request)
    {
        // Validasi data masuk tanpa menahan jadwal_id
        $request->validate([
            'user_id'     => 'required',
            'grand_total' => 'required',
            'sampah_list' => 'required', 
            'foto_sampah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        DB::beginTransaction();

        try {
            // Olah upload gambar bukti
            $pathFoto = null;
            if ($request->hasFile('foto_sampah')) {
                $file = $request->file('foto_sampah');
                $namaFile = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/sampah'), $namaFile);
                $pathFoto = 'uploads/sampah/' . $namaFile;
            }

            // Simpan Data Induk Master (MURNI TANPA JADWAL_ID)
            $setor = new SetorSampah();
            $setor->user_id = $request->user_id;
            $setor->kurir_id = $request->kurir_id ?? 14; 
            $setor->total = $request->grand_total; 
            $setor->foto_sampah = $pathFoto;
            $setor->catatan = $request->catatan ?? 'Disetor massal lewat aplikasi kurir';
            $setor->save();

            // Bongkar paket Array JSON dari Flutter
            $sampahList = json_decode($request->sampah_list, true);

            if (!is_array($sampahList)) {
                return response()->json(['success' => false, 'message' => 'Format sampah_list tidak valid.'], 400);
            }

            // Looping simpan ke tabel Anak (DetailSetorSampah)
            foreach ($sampahList as $item) {
                $detail = new DetailSetorSampah();
                $detail->setor_sampah_id = $setor->id; 
                $detail->jenis_sampah_id = $item['jenis_sampah_id'];
                $detail->berat           = $item['berat'];
                $detail->harga_per_kg    = $item['harga_per_kg'];
                $detail->subtotal        = $item['total_item']; 
                $detail->save();
            }

            // 🔥 1. PROSES FINANSIAL: AMBIL DATA NASABAH & TAMBAH SALDO
            $nasabah = User::find($request->user_id);
            if ($nasabah) {
                $nasabah->saldo += $request->grand_total; // Menambahkan saldo cash berjalan
                $nasabah->save();
            }

            // 🔥 2. PROSES AUDIT: CATAT HISTORI MUTASI SALDO (FIXED COLUMN)
            $mutasi = new MutasiSaldo();
            $mutasi->user_id         = $request->user_id;
            $mutasi->jenis_transaksi = 'masuk'; // Karena saldo bertambah
            $mutasi->sumber          = 'setor_sampah';
            $mutasi->referensi_id    = $setor->id; // Mengunci ID master setor_sampahs (Match dengan Migration!)
            $mutasi->nominal         = $request->grand_total;
            $mutasi->status          = 'success';
            $mutasi->keterangan      = 'Uang masuk dari penimbangan sampah multi-item';
            $mutasi->save();

            DB::commit(); 

            return response()->json([
                'success' => true,
                'message' => '✅ Multi-setor sampah berhasil disimpan! Saldo bertambah & mutasi tercatat.',
                'data' => $setor->load('details')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}