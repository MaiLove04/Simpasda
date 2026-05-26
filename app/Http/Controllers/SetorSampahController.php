<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah; 
use App\Models\DetailSetorSampah; // 🛠️ Panggil model detail
use App\Models\JadwalPenjemputan; 

class SetorSampahController extends Controller
{
    /**
     * AMBIL SEMUA RIWAYAT SETORAN BERDASARKAN KURIR ID
     * (Diselaraskan agar me-load relasi details multi-item)
     */
    public function getRiwayatTotal($kurir_id)
    {
        // Mengambil data setor sampah beserta sub-relasi item rinciannya
        $riwayat = SetorSampah::with(['nasabah', 'details.jenisSampah'])
                    ->where('kurir_id', $kurir_id)
                    ->latest()
                    ->get();

        return response()->json($riwayat, 200);
    }

    /**
     * SIMPAN TRANSAKSI PENIMBANGAN MULTI-ITEM BARU
     */
    public function store(Request $request)
    {
        // 1. Validasi kiriman data baru dari Flutter
        $request->validate([
            'user_id'     => 'required',
            'kurir_id'    => 'required',
            'grand_total' => 'required|numeric', // Akumulasi total uang semua item
            'sampah_list' => 'required',         // String JSON Array dari keranjang Flutter
            'jadwal_id'   => 'required', 
            'foto_sampah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Handle upload file foto bukti sampah (diikat di tabel induk/utama)
        $fotoPath = null;
        if ($request->hasFile('foto_sampah')) {
            $file = $request->file('foto_sampah');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/sampah'), $filename);
            $fotoPath = 'uploads/sampah/' . $filename;
        }

        // 3. Simpan data ke dalam database Induk (Tabel: setor_sampahs)
        $setor = new SetorSampah();
        $setor->user_id = $request->user_id;
        $setor->kurir_id = $request->kurir_id;
        $setor->jadwal_id = $request->jadwal_id;
        $setor->total = $request->grand_total; // Menyimpan grand total
        $setor->catatan = $request->catatan ?? "Disetor massal lewat aplikasi kurir";
        $setor->foto_sampah = $fotoPath; 
        $setor->save();

        // 4. 🛠️ BONGKAR ARRAY JSON & SIMPAN KE TABEL DETAIL (detail_setor_sampahs)
        $items = json_decode($request->sampah_list, true);
        if (is_array($items)) {
            foreach ($items as $item) {
                $detail = new DetailSetorSampah();
                $detail->setor_sampah_id = $setor->id; // Mengikat ke ID induk di atas
                $detail->jenis_sampah_id = $item['jenis_sampah_id'];
                $detail->berat           = $item['berat'];
                $detail->harga_per_kg    = $item['harga_per_kg'];
                $detail->total_harga     = $item['total_item']; // Total per jenis barang
                $detail->save();
            }
        }

        // 5. OTOMATISASI: Update status Jadwal Penjemputan jadi selesai
        $jadwal = JadwalPenjemputan::find($request->jadwal_id);
        if ($jadwal) {
            $jadwal->status = 'selesai';
            $jadwal->save();
        }

        // 6. Kembalikan respon sukses JSON ke Flutter
        return response()->json([
            'success' => true,
            'message' => 'Semua item setoran sampah berhasil disimpan dan status jadwal diperbarui!',
            'data'    => $setor->load('details.jenisSampah') // Sertakan rincian data barunya
        ], 201);
    }
}