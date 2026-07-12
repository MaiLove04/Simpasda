<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah;
use App\Models\DetailSetorSampah;
use App\Models\User;
use App\Models\MutasiSaldo;
use Illuminate\Support\Facades\DB;

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
     * ⚖️ STORE SIMPAN TIMBANGAN KURIR: OTOMATISASI SALDO & UPDATE JADWAL SELESAI
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'     => 'required',
            'grand_total' => 'required',
            'sampah_list' => 'required',
            'foto_sampah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        DB::beginTransaction();

        try {
            // 1. Olah upload gambar bukti penimbangan sampah
            $pathFoto = null;
            if ($request->hasFile('foto_sampah')) {
                $file = $request->file('foto_sampah');
                $namaFile = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/sampah'), $namaFile);
                $pathFoto = 'uploads/sampah/' . $namaFile;
            }

            // 2. Simpan Data Induk Master Setor Sampah
            $setor = new SetorSampah();
            $setor->user_id = $request->user_id;

            // Cari kurir pengganti jika ID 14 tidak ada
            $defaultKurir = User::where('role', 'kurir')->first();
            $setor->kurir_id = $request->kurir_id ?? ($defaultKurir ? $defaultKurir->id : 14);

            $setor->total = $request->grand_total;
            $setor->foto_sampah = $pathFoto;
            $setor->catatan = $request->catatan ?? 'Disetor massal lewat aplikasi kurir';
            $setor->save();

            // 3. Bongkar paket Array JSON dari Flutter & Simpan ke Details (Tabel Anak)
            $sampahList = json_decode($request->sampah_list, true);
            if (!is_array($sampahList)) {
                return response()->json(['success' => false, 'message' => 'Format sampah_list tidak valid.'], 400);
            }

            foreach ($sampahList as $item) {
                $detail = new DetailSetorSampah();
                $detail->setor_sampah_id = $setor->id;
                $detail->jenis_sampah_id = $item['jenis_sampah_id'];
                $detail->berat           = $item['berat'];
                $detail->harga_per_kg    = $item['harga_per_kg'];
                $detail->subtotal        = $item['total_item'];
                $detail->save();
            }

            // 4. PROSES FINANSIAL: UPDATE SALDO NASABAH
            $nasabah = User::find($request->user_id);
            if ($nasabah) {
                $nominalMasuk = (int) $request->grand_total;
                $nasabah->saldo = $nasabah->saldo + $nominalMasuk;
                $nasabah->save();
            }

            // 5. CATAT HISTORI KE TABEL MUTASI SALDO
            $mutasi = new MutasiSaldo();
            $mutasi->user_id         = $request->user_id;
            $mutasi->jenis_transaksi = 'masuk';
            $mutasi->sumber          = 'setor_sampah';
            $mutasi->referensi_id    = $setor->id;
            $mutasi->nominal         = (int) $request->grand_total;
            $mutasi->status          = 'success';
            $mutasi->keterangan      = 'Uang masuk dari penimbangan sampah multi-item';
            $mutasi->save();

            // 6. SINKRONISASI UPDATE STATUS JADWAL JADI SELESAI
            if ($request->has('jadwal_id') && !empty($request->jadwal_id)) {
                $jadwal = \App\Models\JadwalPenjemputan::find($request->jadwal_id);
                if ($jadwal) {
                    $jadwal->status = 'selesai';
                    $jadwal->save();
                }
            }

            // 7. TAMBAH NOTIFIKASI
            // Notifikasi untuk Nasabah
            $totalBerat = 0;
            foreach ($sampahList as $item) {
                $totalBerat += $item['berat'] ?? 0;
            }
            \App\Models\Notifikasi::create([
                'user_id' => $request->user_id,
                'judul' => 'Sampah Selesai Ditimbang',
                'pesan' => 'Sampah Anda telah selesai ditimbang dengan berat total ' . $totalBerat . ' Kg. Saldo Anda bertambah sebesar Rp ' . number_format($request->grand_total, 0, ',', '.') . '.',
                'type' => 'setoran',
                'is_read' => false,
            ]);

            // Notifikasi untuk Kurir
            \App\Models\Notifikasi::create([
                'user_id' => $setor->kurir_id,
                'judul' => 'Setoran Sampah Sukses',
                'pesan' => 'Berhasil memproses setoran sampah untuk nasabah ' . ($nasabah->name ?? 'Nasabah') . ' dengan total Rp ' . number_format($request->grand_total, 0, ',', '.') . '.',
                'type' => 'setoran',
                'is_read' => false,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Setoran sukses! Saldo bertambah & status jadwal otomatis SELESAI.',
                'data' => $setor->load('details')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses setoran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 AMBIL DATA COUNTER REAL-TIME UNTUK DASHBOARD KURIR
     */
    public function getDashboardKurir($kurir_id)
    {
        try {
            // 1. Hitung berapa kali kurir sukses menangani transaksi
            $totalTransaksi = SetorSampah::where('kurir_id', $kurir_id)->count();

            // 2. Ambil semua ID setor sampah milik kurir ini
            $setorIds = SetorSampah::where('kurir_id', $kurir_id)->pluck('id');

            // 3. Hitung akumulasi BERAT BERSIH (Kg) dari tabel anak detail_setor_sampahs
            $totalKgSampah = \App\Models\DetailSetorSampah::whereIn('setor_sampah_id', $setorIds)->sum('berat');

            // 4. Hitung akumulasi total uang rupiah yang sudah diproses oleh kurir ini
            $totalUangDiproses = SetorSampah::where('kurir_id', $kurir_id)->sum('total');

            return response()->json([
                'success' => true,
                'kurir_id' => $kurir_id,
                'total_transaksi' => $totalTransaksi,
                'total_kg_sampah' => round($totalKgSampah, 2),
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
     * 📥 FIX SINKRONISASI FLUTTER NASABAH: REQUEST JEMPUT SAMPAH (MEMBACA STRUKTUR ITEMS)
     */
    /**
     * 📥 ALUR FINAL NASABAH: REQUEST JEMPUT & DAFTARKAN MANIFES JENIS SAMPAH KOSONG
     */
    public function requestPenjemputan(Request $request)
    {
        // Validasi murni melacak data bawaan dari Flutter kamu
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items'   => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            // 1. Ambil data profil nasabah untuk mendeteksi alamat aslinya
            $nasabah = User::find($request->user_id);

            // 🔥 PERBAIKAN: Cari kurir yang benar-benar ada di database (dinamis)
            $kurirAktif = User::where('role', 'kurir')->first();
            $kurirIdDefault = $kurirAktif ? $kurirAktif->id : 14;

            // 2. SIMPAN KE TABEL jadwal_penjemputans (Murni Catatan Nasabah)
            $jadwal = new \App\Models\JadwalPenjemputan();
            $jadwal->nasabah_id = $request->user_id;
            $jadwal->kurir_id = $kurirIdDefault;
            $jadwal->bank_sampah_id = 1; // Default Lokasi Bank Sampah Basayan Bestari
            $jadwal->alamat = $nasabah->alamat ?? 'Alamat tidak diisi nasabah';
            $jadwal->tanggal_penjemputan = \Carbon\Carbon::today()->format('Y-m-d');
            $jadwal->status = 'terjadwal'; // Status awal: Menunggu kurir bergerak
            $jadwal->catatan = $request->catatan ?? 'Disetor lewat aplikasi nasabah'; // 🔥 MURNI CATATAN NASABAH
            $jadwal->save();

            // 3. SIMPAN KE TABEL INDUK setor_sampahs (Manifes Awal)
            $setor = new SetorSampah();
            $setor->user_id = $request->user_id;
            $setor->kurir_id = $kurirIdDefault;
            $setor->total = 0; // Masih 0 karena belum ditimbang kurir
            $setor->foto_sampah = null; // Belum ada foto bukti timbangan
            $setor->catatan = $request->catatan ?? 'Request jemput lewat aplikasi';
            $setor->status = 'menunggu_verifikasi'; // 🔥 Sesuai status di phpMyAdmin kamu!
            $setor->save();

            // 4. BONGKAR ARRAY ITEMS FLUTTER & SIMPAN KE detail_setor_sampahs (Berat = NULL / 0)
            foreach ($request->items as $item) {
                if (isset($item['jenis_sampah_id'])) {
                    $detail = new DetailSetorSampah();
                    $detail->setor_sampah_id = $setor->id; // Hubungkan ke ID induk barusan
                    $detail->jenis_sampah_id = $item['jenis_sampah_id'];
                    $detail->berat           = null; // 🔥 Kosong, nanti di-update oleh Kurir via IoT
                    $detail->harga_per_kg    = null; // 🔥 Kosong, di-update otomatis saat kurir nimbang
                    $detail->subtotal        = 0;
                    $detail->save();
                }
            }

            // TAMBAH NOTIFIKASI
            // Notifikasi untuk Courier
            \App\Models\Notifikasi::create([
                'user_id' => $kurirIdDefault,
                'judul' => 'Tugas Penjemputan Baru',
                'pesan' => 'Ada request penjemputan sampah baru dari nasabah ' . ($nasabah->name ?? 'Nasabah') . ' di alamat: ' . $jadwal->alamat . '.',
                'type' => 'penjemputan',
                'is_read' => false,
            ]);

            // Notifikasi untuk Nasabah
            \App\Models\Notifikasi::create([
                'user_id' => $request->user_id,
                'judul' => 'Permintaan Penjemputan Terkirim',
                'pesan' => 'Permintaan penjemputan sampah Anda telah terkirim. Kurir akan segera datang ke lokasi Anda.',
                'type' => 'penjemputan',
                'is_read' => false,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Request penjemputan & manifes kategori sampah berhasil didaftarkan!',
                'data' => [
                    'jadwal' => $jadwal,
                    'setor' => $setor->load('details')
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

    //request detail
    /**
     * 🔍 BARU: AMBIL MANIFES REQUEST NASABAH UNTUK OTOMATISASI FORM TIMBANG KURIR
     */
    public function showRequestDetail($nasabah_id)
    {
        try {
            // Cari transaksi terakhir nasabah yang statusnya masih 'menunggu_verifikasi'
            $setorMaster = SetorSampah::where('user_id', $nasabah_id)
                ->where('status', 'menunggu_verifikasi')
                ->latest()
                ->first();

            if (!$setorMaster) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada manifes request aktif untuk nasabah ini.'
                ], 404);
            }

            // Ambil rincian jenis sampah kosong yang dititipkan nasabah kemarin
            $details = \App\Models\DetailSetorSampah::where('setor_sampah_id', $setorMaster->id)
                ->with('jenisSampah') // Relasi ke tabel jenis_sampahs
                ->get();

            // Susun data agar Flutter Kurir bisa langsung me-mapping ke keranjang setorannya
            $formAutoload = [];
            foreach ($details as $item) {
                if ($item->jenisSampah) {
                    // 🔥 DETEKSI OTOMATIS: Mengantisipasi segala nama kolom harga di database kamu
                    $hargaBeliTerupdate = $item->jenisSampah->harga_beli
                                        ?? $item->jenisSampah->harga
                                        ?? $item->jenisSampah->harga_per_kg
                                        ?? 2000; // Fallback aman angka 2000 jika semua kolom null

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
                'success' => true,
                'setor_sampah_id' => $setorMaster->id, // ID Induk untuk nanti kurir update
                'items' => $formAutoload
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal meload data request: ' . $e->getMessage()
            ], 500);
        }
    }
}
