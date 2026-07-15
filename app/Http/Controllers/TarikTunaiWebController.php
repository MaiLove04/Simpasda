<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\TarikTunai;
use App\Models\MutasiSaldo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TarikTunaiWebController extends Controller
{
    /**
     * Menampilkan daftar request tarik tunai yang pending
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');

        // 🌟 MATIKAN FILTER SEMENTARA UNTUK TESTING
        $requests = TarikTunai::with('user')
            ->where('status', 'pending')
            ->when($keyword, function ($query, $keyword) {
                return $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('kode_nasabah', 'LIKE', '%' . $keyword . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('admin.tarik-tunai.index', compact('requests'));
    }

    /**
     * 🔥 APPROVE VIA WEB: Baru potong saldo utama di sini
     */
    public function approve($id)
    {
        $tarikTunai = TarikTunai::findOrFail($id);

        if ($tarikTunai->status !== 'pending') {
            return back()->with('error', 'Request ini sudah diproses sebelumnya.');
        }

        $nasabah = $tarikTunai->user;

        if ($nasabah->saldo < $tarikTunai->jumlah_nominal) {
            return back()->with('error', 'Saldo nasabah tidak mencukupi untuk penarikan ini.');
        }

        DB::beginTransaction();
        try {
            // 1. Baru potong saldo di sini karena disetujui admin web
            $nasabah->saldo -= $tarikTunai->jumlah_nominal;
            $nasabah->save();

            // 2. Update status request utama
            $tarikTunai->update([
                'status' => 'approved',
                'tanggal_selesai' => now()
            ]);

            // 3. Ubah status di mutasi_saldos nasabah terkait menjadi success
            $mutasi = MutasiSaldo::where('user_id', $tarikTunai->user_id)
                ->where('sumber', 'tarik_tunai')
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($mutasi) {
                $mutasi->update([
                    'status' => 'success',
                    'keterangan' => 'Penarikan tunai disetujui oleh admin bank sampah'
                ]);
            }

            // Kirim notifikasi ke nasabah
            Notifikasi::create([
                'user_id' => $nasabah->id,
                'judul' => 'Penarikan Dana Disetujui',
                'pesan' => 'Pengajuan penarikan dana Anda sebesar Rp ' . number_format($tarikTunai->jumlah_nominal, 0, ',', '.') . ' telah disetujui.',
                'type' => 'transaksi'
            ]);

            DB::commit();
            return back()->with('success', 'Request penarikan tunai berhasil disetujui, saldo nasabah telah dipotong.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 REJECT VIA WEB: Saldo tetap utuh (tidak berubah)
     */
    public function reject($id)
    {
        $tarikTunai = TarikTunai::findOrFail($id);

        if ($tarikTunai->status !== 'pending') {
            return back()->with('error', 'Request ini sudah diproses sebelumnya.');
        }

        DB::beginTransaction();
        try {
            // 1. Update status request utama menjadi rejected
            $tarikTunai->update([
                'status' => 'rejected',
                'tanggal_selesai' => now()
            ]);

            // 2. Ubah status mutasi saldo nasabah terkait menjadi rejected
            $mutasi = MutasiSaldo::where('user_id', $tarikTunai->user_id)
                ->where('sumber', 'tarik_tunai')
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($mutasi) {
                $mutasi->update([
                    'status' => 'rejected',
                    'keterangan' => 'Penarikan tunai ditolak oleh admin bank sampah'
                ]);
            }

            // Kirim notifikasi ke nasabah
            Notifikasi::create([
                'user_id' => $tarikTunai->user_id,
                'judul' => 'Penarikan Dana Ditolak',
                'pesan' => 'Mohon maaf, pengajuan penarikan dana Anda ditolak oleh admin.',
                'type' => 'transaksi'
            ]);

            DB::commit();
            return back()->with('success', 'Request penarikan tunai berhasil ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan riwayat penarikan
     */
    /**
     * PERBAIKAN: Menampilkan semua riwayat penarikan (Pending, Approved, Rejected)
     */
    public function riwayat(Request $request)
    {
        $keyword = $request->get('search');
        $bankSampahId = Auth::user()->bank_sampah_id;

        $riwayat = TarikTunai::with('user')
            ->whereHas('user', function ($query) use ($bankSampahId) {
                // Jika ingin mematikan filter bank sampah seperti halaman index, 
                // kamu bisa mengomentari baris di bawah ini dengan (//)
                $query->where('bank_sampah_id', $bankSampahId);
            })
            // 🔥 PERUBAHAN UTAMA: Izinkan status 'pending' ikut masuk ke dalam list riwayat
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->when($keyword, function ($query, $keyword) {
                return $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%')
                      ->orWhere('kode_nasabah', 'LIKE', '%' . $keyword . '%');
                });
            })
            // Urutkan berdasarkan data yang paling baru dibuat atau diajukan
            ->latest('created_at')
            ->paginate(15);

        return view('admin.tarik-tunai.riwayat', compact('riwayat'));
    }
}