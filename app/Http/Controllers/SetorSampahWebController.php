<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetorSampah;
use App\Models\DetailSetorSampah;
use Illuminate\Support\Facades\Auth;

class SetorSampahWebController extends Controller
{
    /**
     * MENAMPILKAN LOG PENYETORAN SAMPAH + FITUR SEARCH & PAGINATION (MAKSIMAL 7 DATA)
     */
    public function index(Request $request)
    {
        // Ambil kata kunci pencarian dari input bernama 'search'
        $keyword = $request->get('search');

        // Query data beserta relasinya dengan pencarian & pagination maksimal 7 data
        $dataSetor = SetorSampah::with(['nasabah', 'kurir', 'details.jenisSampah'])
            ->whereHas('nasabah', function ($q) {
                $q->where('bank_sampah_id', Auth::user()->bank_sampah_id);
            })
            ->when($keyword, function ($query, $keyword) {
                return $query->where(function ($q) use ($keyword) {
                    $q->whereHas('nasabah', function ($sub) use ($keyword) {
                        $sub->where('name', 'LIKE', '%' . $keyword . '%');
                    })->orWhereHas('kurir', function ($sub) use ($keyword) {
                        $sub->where('name', 'LIKE', '%' . $keyword . '%');
                    });
                });
            })
            ->latest()
            ->paginate(5); // 📑 KUNCI UTAMA: Diubah menjadi 5 data per halaman

        return view('admin.setor-sampah.index', compact('dataSetor'));
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
     * UPDATE STATUS SETOR SAMPAH (Misal verifikasi penyelesaian)
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
     * TAMPILKAN FORM INPUT SETOR SAMPAH MANUAL
     */
    public function formManual($id)
    {
        // Mengambil data nasabah
        $nasabah = \App\Models\User::where('role', 'nasabah')->findOrFail($id);
        
        // Mengambil semua jenis sampah aktif untuk pilihan di form
        $jenisSampah = \App\Models\JenisSampah::all(); 

        return view('admin.setor-sampah.manual', compact('nasabah', 'jenisSampah'));
    }

    /**
     * PROSES SIMPAN TRANSAKSI SETOR SAMPAH & TAMBAH SALDO
     */
    public function prosesManual(Request $request, $id)
    {
        $request->validate([
            'jenis_sampah_id' => 'required|exists:jenis_sampahs,id',
            'berat' => 'required|numeric|min:0.1',
        ]);

        $nasabah = \App\Models\User::where('role', 'nasabah')->findOrFail($id);
        $jenis = \App\Models\JenisSampah::findOrFail($request->jenis_sampah_id);

        // Hitung total harga (berat x harga per kg)
        $totalHarga = $request->berat * $jenis->harga_per_kg;

        \DB::beginTransaction();
        try {
            // 1. Simpan ke Tabel Setor Sampah (Tabungan Sampah)
            // Sesuaikan nama model dan kolomnya dengan struktur database kamu
            $setor = new \App\Models\SetorSampah(); 
            $setor->user_id = $nasabah->id;
            $setor->jenis_sampah_id = $jenis->id;
            $setor->berat = $request->berat;
            $setor->total_harga = $totalHarga;
            $setor->status = 'success'; // Langsung sukses karena disetor manual di loket
            $setor->keterangan = 'Setoran sampah manual via Loket Admin';
            $setor->save();

            // 2. Tambah Saldo Utama Nasabah
            $nasabah->saldo += $totalHarga;
            $nasabah->save();

            // 3. Catat Histori ke Tabel Mutasi Saldo
            $mutasi = new \App\Models\MutasiSaldo();
            $mutasi->user_id = $nasabah->id;
            $mutasi->jenis_transaksi = 'masuk';
            $mutasi->sumber = 'setor_sampah';
            $mutasi->nominal = $totalHarga;
            $mutasi->status = 'success';
            $mutasi->keterangan = 'Setoran sampah (' . $jenis->nama_sampah . ' - ' . $request->berat . ' kg)';
            $mutasi->save();

            \DB::commit();

            // Arahkan kembali ke halaman setor sampah indeks dengan pesan sukses
            return redirect('/admin/setor-sampah')->with('success', 'Setoran manual berhasil! Saldo ' . $nasabah->name . ' bertambah Rp ' . number_format($totalHarga, 0, ',', '.'));
            } catch (\Exception $e) {
                \DB::rollBack();
                return back()->with('error', 'Gagal memproses setoran: ' . $e->getMessage());
            }
    }
}