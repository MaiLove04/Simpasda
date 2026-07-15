<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\MutasiSaldo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; //

class NasabahWebController extends Controller
{
    /**
     * MENAMPILKAN DAFTAR NASABAH + FILTER SEARCH & PAGINATION (MAKSIMAL 7 DATA PER CABANG)
     */
    public function index(Request $request)
    {
        // Ambil kata kunci pencarian dari input search bar
        $keyword = $request->get('search');

        // Mengunci data berdasarkan Cabang Admin Login, Role Nasabah, Fitur Search, dan Paging 7 Data
        $nasabahs = User::where('role', 'nasabah')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->when($keyword, function ($query, $keyword) {
                // Kunci pencarian di dalam bracket khusus (grouping WHERE) agar tidak merusak validasi bank_sampah_id
                return $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%')
                      ->orWhere('kode_nasabah', 'LIKE', '%' . $keyword . '%');
                });
            })
            ->latest()
            ->paginate(7); // 📑 SINKRON: Maksimal 7 data per halaman dan membuang error firstItem()

        return view('admin.nasabah.index', compact('nasabahs'));
    }

    /**
     * MENAMPILKAN DETAIL DATA NASABAH
     */
    public function show($id)
    {
        $nasabah = User::findOrFail($id);

        return view('admin.nasabah.show', compact('nasabah'));
    }

    /**
     * APPROVAL INSTAN AKUN NASABAH MENJADI AKTIF
     */
    public function approve($id)
    {
        $nasabah = User::findOrFail($id);
        $nasabah->update([
            'status' => 'aktif'
        ]);

        // Kirim notifikasi ke nasabah bahwa akunnya sudah aktif
        Notifikasi::create([
            'user_id' => $nasabah->id,
            'judul' => 'Akun Anda Telah Aktif!',
            'pesan' => 'Selamat! Akun Anda telah disetujui. Kini Anda dapat mulai menabung sampah dan menikmati layanan kami.',
            'type' => 'akun'
        ]);

        return redirect('/admin/nasabah')->with('success', 'Akun nasabah berhasil disetujui dan diaktifkan!');
    }

    /**
     * MENGHAPUS DATA NASABAH FROM DATABASE
     */
    public function destroy($id)
    {
        $nasabah = User::findOrFail($id);
        $nasabah->delete();

        return redirect('/admin/nasabah')->with('success', 'Data akun nasabah berhasil dihapus permanen.');
    }
    
    /**
     * UPDATE STATUS NASABAH VIA DROPDOWN DI TABEL UTAMA
     */
    public function updateStatus(Request $request, $id) 
    {
        $request->validate([
            'status' => 'required'
        ]);

        $nasabah = User::findOrFail($id);
        $nasabah->update([
            'status' => $request->status
        ]);

        return redirect('/admin/nasabah')->with('success', 'Status verifikasi nasabah berhasil diperbarui!');
    }

    public function printQr($id)
    {
        $nasabah = User::findOrFail($id);
        
        // Membuka view khusus print
        return view('admin.nasabah.print-qr', compact('nasabah'));
    }

    //tampil nasabah untuk tarik tunai
    /**
     * MENAMPILKAN DAFTAR NASABAH UNTUK LOKET KASIR TARIK TUNAI
     */
    public function indexTarikTunai(Request $request)
    {
        $keyword = $request->get('search');

        // Mengunci data berdasarkan Cabang Admin Login, Role Nasabah, Fitur Search, dan Paging 10 Data
        $nasabahs = User::where('role', 'nasabah')
            ->where('bank_sampah_id', Auth::user()->bank_sampah_id)
            ->when($keyword, function ($query, $keyword) {
                return $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%')
                      ->orWhere('kode_nasabah', 'LIKE', '%' . $keyword . '%');
                });
            })
            ->latest()
            ->paginate(10); // Menggunakan paginasi 10 agar berbeda dengan halaman nasabah biasa

        return view('admin.tarik-tunai.index', compact('nasabahs'));
    }

    /**
     * TAMPILKAN FORM INPUT NOMINAL TARIK TUNAI OLEH ADMIN
     */
    public function tarikTunai($id)
    {
        $nasabah = User::findOrFail($id);
        
        // Proteksi keamanan: Pastikan yang diakses benar nasabah dan satu cabang bank sampah dengan admin
        if ($nasabah->role !== 'nasabah' || $nasabah->bank_sampah_id !== Auth::user()->bank_sampah_id) {
            return redirect()->route('admin.tarik-tunai.index')->with('error', 'Nasabah tidak valid atau tidak memiliki akses.');
        }

        return view('admin.tarik-tunai.form', compact('nasabah'));
    }

    /**
     * PROSES EKSEKUSI POTONG SALDO DAN SIMPAN MUTASI KELUAR
     */
    public function prosesTarikTunai(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
        ]);

        $nasabah = User::findOrFail($id);

        if ($nasabah->role !== 'nasabah' || $nasabah->bank_sampah_id !== Auth::user()->bank_sampah_id) {
            return redirect()->route('admin.tarik-tunai.index')->with('error', 'Nasabah tidak valid atau tidak memiliki akses.');
        }

        // Cek validasi kecukupan saldo sebelum dipotong
        if ($nasabah->saldo < $request->nominal) {
            return back()->with('error', 'Saldo nasabah tidak mencukupi untuk melakukan penarikan sebesar Rp ' . number_format($request->nominal, 0, ',', '.'));
        }

        DB::beginTransaction();
        try {
            // 1. Potong Saldo Utama Nasabah
            $nasabah->saldo -= $request->nominal;
            $nasabah->save();

            // 2. Catat Histori ke Tabel Mutasi Saldo
            $mutasi = new MutasiSaldo();
            $mutasi->user_id = $nasabah->id;
            $mutasi->jenis_transaksi = 'keluar';
            $mutasi->sumber = 'tarik_tunai';
            $mutasi->nominal = $request->nominal;
            $mutasi->status = 'success';
            $mutasi->keterangan = 'Penarikan tunai saldo tabungan sampah (via Admin Loket)';
            $mutasi->save();

            DB::commit();

            return redirect()->route('admin.tarik-tunai.index')->with('success', 'Penarikan tunai sebesar Rp ' . number_format($request->nominal, 0, ',', '.') . ' berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memproses penarikan: ' . $e->getMessage());
        }
    }

    /**
     * MENAMPILKAN LOG JURNAL RIWAYAT PENARIKAN SALDO KASIR
     */
    public function riwayatPenarikan(Request $request)
    {
        $keyword = $request->get('search');
        $date = $request->get('date');

        // Mengambil data dari tabel MutasiSaldo khusus transaksi 'keluar' dari sumber 'tarik_tunai'
        // Serta membatasi data hanya untuk nasabah yang satu cabang dengan admin login
        $riwayat = MutasiSaldo::with('user')
            ->where('jenis_transaksi', 'keluar')
            ->where('sumber', 'tarik_tunai')
            ->whereHas('user', function ($query) {
                $query->where('bank_sampah_id', Auth::user()->bank_sampah_id);
            })
            ->when($keyword, function ($query, $keyword) {
                return $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%')
                      ->orWhere('kode_nasabah', 'LIKE', '%' . $keyword . '%');
                });
            })
            ->when($date, function ($query, $date) {
                return $query->whereDate('created_at', $date);
            })
            ->latest()
            ->paginate(15);

        return view('admin.tarik-tunai.riwayat', compact('riwayat'));
    }
}