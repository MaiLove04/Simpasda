<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\MutasiSaldo;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_sampah' => 'required',
            'catatan' => 'nullable',
        ]);

        $transaksi = Transaksi::create([
            'user_id' => 1, 
            'jenis_sampah' => $validated['jenis_sampah'],
            'catatan' => $validated['catatan'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Transaksi berhasil dibuat',
            'data' => $transaksi
        ]);
    }

    /**
     * 🔥 SINKRONISASI APPROVE: Potong saldo utama nasabah di sini
     */
    public function approveTarikTunai($id)
    {
        $mutasi = MutasiSaldo::where('id', $id)
            ->where('sumber', 'tarik_tunai')
            ->where('status', 'pending')
            ->first();

        if (!$mutasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengajuan penarikan tidak ditemukan atau sudah diproses.'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // 1. POTONG SALDO DI SINI KARENA DI-APPROVE
            $user = User::find($mutasi->user_id);
            if (!$user || $user->saldo < $mutasi->nominal) {
                return response()->json(['success' => false, 'message' => 'Saldo nasabah tidak mencukupi.'], 400);
            }
            
            $user->saldo -= $mutasi->nominal;
            $user->save();

            // 2. Ubah status mutasi menjadi sukses
            $mutasi->status = 'success';
            $mutasi->keterangan = str_replace(' (Menunggu Persetujuan)', '', $mutasi->keterangan);
            $mutasi->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Penarikan disetujui, saldo nasabah berhasil dipotong.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 🔥 SINKRONISASI REJECT: Jangan ubah saldo karena dari awal belum dipotong
     */
    public function rejectTarikTunai($id)
    {
        $mutasi = MutasiSaldo::where('id', $id)
            ->where('sumber', 'tarik_tunai')
            ->where('status', 'pending')
            ->first();

        if (!$mutasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengajuan penarikan tidak ditemukan atau sudah diproses.'
            ], 404);
        }

        // Cukup ubah status mutasi menjadi rejected tanpa menyentuh kolom saldo user
        $mutasi->status = 'rejected';
        $mutasi->keterangan = "Ditolak Admin: " . str_replace(' (Menunggu Persetujuan)', '', $mutasi->keterangan);
        $mutasi->save();

        return response()->json([
            'success' => true,
            'message' => 'Penarikan ditolak. Saldo nasabah tetap utuh.'
        ], 200);
    }
}