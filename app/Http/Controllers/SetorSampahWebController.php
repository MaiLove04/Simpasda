<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah;
use App\Models\DetailSetorSampah;
use App\Models\JenisSampah;
use App\Models\User;
use App\Models\MutasiSaldo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetorSampahWebController extends Controller
{
    /**
     * MENAMPILKAN LOG PENYETORAN SAMPAH + FITUR SEARCH & PAGINATION (MAKSIMAL 7 DATA)
     */
    public function index(Request $request)
    {
        $keyword = $request->search;
        $bulan   = $request->bulan;
    
        $dataSetor = SetorSampah::with([
                'nasabah',
                'kurir',
                'details.jenisSampah'
            ])
            ->whereHas('nasabah', function ($q) {
                $q->where('bank_sampah_id', Auth::user()->bank_sampah_id);
            })
    
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
    
                    $q->whereHas('nasabah', function ($sub) use ($keyword) {
                        $sub->where('name', 'like', "%{$keyword}%");
                    })
    
                    ->orWhereHas('kurir', function ($sub) use ($keyword) {
                        $sub->where('name', 'like', "%{$keyword}%");
                    });
    
                });
            })
    
            ->when($bulan, function ($query) use ($bulan) {
                $query->whereYear('created_at', date('Y', strtotime($bulan)))
                      ->whereMonth('created_at', date('m', strtotime($bulan)));
            })
    
            ->latest()
            ->paginate(7) // Disesuaikan menjadi 7 sesuai dokumentasi method Anda
            ->withQueryString();
    
        return view('admin.setor-sampah.index', compact('dataSetor'));
    }

    /**
     * ➕ TAMBAHAN BARU: TAMPILKAN FORM LOKET MANUAL UMUM (DARI TOMBOL BERANDA)
     */
    public function createManual()
    {
        // Mengambil daftar semua nasabah aktif di bawah naungan unit bank sampah ini
        $dataNasabah = User::where('role', 'nasabah')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->get();

        // Mengambil semua tipe/jenis jenis sampah yang aktif
        $jenisSampah = JenisSampah::all();

        return view('admin.setor-sampah.create_manual', compact('dataNasabah', 'jenisSampah'));
    }

    /**
     * ➕ TAMBAHAN BARU: PROSES SIMPAN TRANSAKSI LOKET MANUAL MULTI-ITEM
     */
    public function storeManual(Request $request)
{
    $request->validate([
        'nasabah_id' => 'required|exists:users,id',
        'items' => 'required|array|min:1',
        'items.*.jenis_sampah_id' => 'required|exists:jenis_sampahs,id',
        'items.*.berat' => 'required|numeric|min:0.1',
    ]);

    // Menggunakan model User langsung (Pastikan di bagian atas file sudah ada: use App\Models\User;)
    $nasabah = User::where('role', 'nasabah')->findOrFail($request->nasabah_id);
    
    DB::beginTransaction();
    try {
        // 1. Ambil data item pertama untuk mengisi field wajib di tabel induk setor_sampahs
        $firstItem = $request->items[0];
        $jenisPertama = JenisSampah::findOrFail($firstItem['jenis_sampah_id']);

        // 2. Inisiasi Induk Transaksi Setor Sampah
        $setor = new SetorSampah();
        $setor->user_id = $nasabah->id; 
        $setor->kurir_id = null; 
        $setor->status = 'Selesai'; // Pastikan di DB kamu string 'Selesai' didukung atau diubah ke 'success' jika enum
        
        // 🔥 WAJIB DIISI: Mengisi field-field yang NOT NULL / ada di tabel induk berdasarkan item pertama
        $setor->jenis_sampah_id = $jenisPertama->id;
        $setor->harga_per_kg = $jenisPertama->harga_per_kg;
        $setor->berat = $firstItem['berat']; 
        $setor->total = 0; // Set awal 0, akan diupdate setelah looping detail
        $setor->save();

        $grandTotal = 0;
        $totalBeratAkumulasi = 0;

        // 3. Loop & Simpan Rincian Item ke Keranjang Detail Sampah
        foreach ($request->items as $item) {
            $jenis = JenisSampah::findOrFail($item['jenis_sampah_id']);
            $subTotal = $item['berat'] * $jenis->harga_per_kg;

            $detail = new DetailSetorSampah();
            $detail->setor_sampah_id = $setor->id; // Mengikat UUID dari $setor->id yang baru di-save
            $detail->jenis_sampah_id = $jenis->id;
            $detail->berat = $item['berat'];
            $detail->harga_per_kg = $jenis->harga_per_kg;
            $detail->subtotal = $subTotal; 
            $detail->save();

            $grandTotal += $subTotal;
            $totalBeratAkumulasi += $item['berat'];
        }

        // 4. Update akumulasi total akhir & total berat ke baris induk transaksi
        $setor->update([
            'total' => $grandTotal,
            'berat' => $totalBeratAkumulasi
        ]); 

        // 5. Update akumulasi saldo dompet nasabah
        $nasabah->saldo += $grandTotal;
        $nasabah->save();

        // 6. Pencatatan mutasi kredit/masuk keuangan nasabah
        $mutasi = new MutasiSaldo();
        $mutasi->user_id = $nasabah->id;
        $mutasi->jenis_transaksi = 'masuk';
        $mutasi->sumber = 'setor_sampah';
        $mutasi->nominal = $grandTotal;
        $mutasi->status = 'success';
        $mutasi->keterangan = 'Setor ' . $totalBeratAkumulasi . ' kg sampah via Loket Manual Admin';
        $mutasi->save();

        DB::commit();
        return redirect()->route('admin.setor.index')->with('success', 'Setoran loket manual nasabah ' . $nasabah->name . ' berhasil dibukukan!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal memproses transaksi loket: ' . $e->getMessage())->withInput();
    }
}

    /**
     * MENAMPILKAN DETAIL DATA SETOR SAMPAH (RINCIAN ITEM & HARGA)
     */
    public function show($id)
    {
        $setor = SetorSampah::with(['nasabah', 'kurir', 'details.jenisSampah'])
            ->whereHas('nasabah', function ($q) {
                $q->where('bank_sampah_id', Auth::user()->bank_sampah_id);
            })->findOrFail($id);
            
        return view('admin.setor-sampah.show', compact('setor'));
    }

    /**
     * UPDATE STATUS SETOR SAMPAH
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required']);

        $setor = SetorSampah::whereHas('nasabah', function ($q) {
            $q->where('bank_sampah_id', Auth::user()->bank_sampah_id);
        })->findOrFail($id);
        
        $setor->update(['status' => $request->status]);

        return back()->with('success', 'Status setor sampah berhasil diperbarui!');
    }

    /**
     * MENGHAPUS DATA SETOR SAMPAH BESERTA DETAIL MANIFESNYA
     */
    public function destroy($id)
    {
        $setor = SetorSampah::whereHas('nasabah', function ($q) {
            $q->where('bank_sampah_id', Auth::user()->bank_sampah_id);
        })->findOrFail($id);
        
        // Hapus detail / isi keranjangnya terlebih dahulu agar tidak menumpuk jadi file sampah di DB
        DetailSetorSampah::where('setor_sampah_id', $id)->delete();
        
        $setor->delete();

        return redirect()->route('admin.setor.index')->with('success', 'Data log setor sampah berhasil dihapus!');
    }

    /**
     * 📟 AMBIL BERAT TERBARU DARI TIMBANGAN IOT (SIMULASI/AKTUAL)
     */
    public function getBeratIot()
    {
        $berat = rand(5, 15) + (rand(0, 9) / 10);
        
        return response()->json([
            'success' => true,
            'berat_iot' => $berat
        ], 200);
    }

    /**
     * TAMPILKAN FORM INPUT SETOR SAMPAH MANUAL SPESIFIK USER ID
     */
    public function formManual($id)
    {
        $nasabah = User::where('role', 'nasabah')->findOrFail($id);
        $jenisSampah = JenisSampah::all(); 

        return view('admin.setor-sampah.manual', compact('nasabah', 'jenisSampah'));
    }

    /**
     * PROSES SIMPAN TRANSAKSI SETOR SAMPAH SINGLE-ITEM & TAMBAH SALDO
     */
    public function prosesManual(Request $request, $id)
    {
        $request->validate([
            'jenis_sampah_id' => 'required|exists:jenis_sampahs,id',
            'berat' => 'required|numeric|min:0.1',
        ]);

        $nasabah = User::where('role', 'nasabah')->findOrFail($id);
        $jenis = JenisSampah::findOrFail($request->jenis_sampah_id);

        $totalHarga = $request->berat * $jenis->harga_per_kg;

        DB::beginTransaction();
        try {
            $setor = new SetorSampah(); 
            $setor->nasabah_id = $nasabah->id; // Menyesuaikan relasi tabel log data Anda
            $setor->total = $totalHarga;
            $setor->status = 'Selesai';
            $setor->save();

            // Simpan rincian ke tabel detail
            $detail = new DetailSetorSampah();
            $detail->setor_sampah_id = $setor->id;
            $detail->jenis_sampah_id = $jenis->id;
            $detail->berat = $request->berat;
            $detail->harga_per_kg = $jenis->harga_per_kg;
            $detail->subtotal = $totalHarga;
            $detail->save();

            $nasabah->saldo += $totalHarga;
            $nasabah->save();

            $mutasi = new MutasiSaldo();
            $mutasi->user_id = $nasabah->id;
            $mutasi->jenis_transaksi = 'masuk';
            $mutasi->sumber = 'setor_sampah';
            $mutasi->nominal = $totalHarga;
            $mutasi->status = 'success';
            $mutasi->keterangan = 'Setoran sampah (' . $jenis->nama . ' - ' . $request->berat . ' kg)';
            $mutasi->save();

            DB::commit();

            return redirect()->route('admin.setor.index')->with('success', 'Setoran manual berhasil! Saldo ' . $nasabah->name . ' bertambah Rp ' . number_format($totalHarga, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses setoran: ' . $e->getMessage());
        }
    }
}