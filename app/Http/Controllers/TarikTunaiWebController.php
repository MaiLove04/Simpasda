<?php

namespace App\Http\Controllers;

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
        $bankSampahId = Auth::user()->bank_sampah_id;

        $requests = TarikTunai::with('user')
            ->whereHas('user', function ($query) use ($bankSampahId) {
                $query->where('bank_sampah_id', $bankSampahId);
            })
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
     * Menyetujui request tarik tunai
     */
    public function approve($id)
    {
        $tarikTunai = TarikTunai::findOrFail($id);

        if ($tarikTunai->status !== 'pending') {
            return back()->with('error', 'Request ini sudah diproses sebelumnya.');
        }

        $nasabah = $tarikTunai->user;

        // Cek saldo nasabah sekali lagi
        if ($nasabah->saldo < $tarikTunai->jumlah_nominal) {
            return back()->with('error', 'Saldo nasabah tidak mencukupi untuk penarikan ini.');
        }

        DB::beginTransaction();
        try {
            // 1. Potong saldo nasabah
            $nasabah->saldo -= $tarikTunai->jumlah_nominal;
            $nasabah->save();

            // 2. Update status request
            $tarikTunai->update([
                'status' => 'approved',
                'tanggal_selesai' => now()
            ]);

            // 3. Catat ke mutasi saldo
            MutasiSaldo::create([
                'user_id' => $nasabah->id,
                'jenis_transaksi' => 'keluar',
                'sumber' => 'tarik_tunai',
                'referensi_id' => $tarikTunai->id,
                'nominal' => $tarikTunai->jumlah_nominal,
                'status' => 'success',
                'keterangan' => 'Penarikan tunai disetujui oleh admin bank sampah'
            ]);

            DB::commit();
            return back()->with('success', 'Request penarikan tunai berhasil disetujui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menolak request tarik tunai
     */
    public function reject($id)
    {
        $tarikTunai = TarikTunai::findOrFail($id);

        if ($tarikTunai->status !== 'pending') {
            return back()->with('error', 'Request ini sudah diproses sebelumnya.');
        }

        $tarikTunai->update([
            'status' => 'rejected',
            'tanggal_selesai' => now()
        ]);

        return back()->with('success', 'Request penarikan tunai berhasil ditolak.');
    }

    /**
     * Menampilkan riwayat penarikan (yang sudah approved/rejected)
     */
    public function riwayat(Request $request)
    {
        $keyword = $request->get('search');
        $bankSampahId = Auth::user()->bank_sampah_id;

        $riwayat = TarikTunai::with('user')
            ->whereHas('user', function ($query) use ($bankSampahId) {
                $query->where('bank_sampah_id', $bankSampahId);
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->when($keyword, function ($query, $keyword) {
                return $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%')
                      ->orWhere('kode_nasabah', 'LIKE', '%' . $keyword . '%');
                });
            })
            ->latest('tanggal_selesai')
            ->paginate(15);

        return view('admin.tarik-tunai.riwayat', compact('riwayat'));
    }
}
