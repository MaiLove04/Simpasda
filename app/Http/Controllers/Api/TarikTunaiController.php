<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TarikTunai;
use App\Models\User;
use App\Models\MutasiSaldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TarikTunaiController extends Controller
{
    /**
     * Tampilkan semua request (Admin) atau milik user tertentu (Nasabah)
     */
    public function index(Request $request)
    {
        $query = TarikTunai::with('user');

        if ($request->user()->role === 'nasabah') {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('tanggal_request', 'desc')->get());
    }

    /**
     * Nasabah membuat request penarikan tunai
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_nominal' => 'required|integer|min:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Gunakan DB Transaction dari awal pembuatan request
        return DB::transaction(function () use ($request) {
            // Menggunakan lockForUpdate untuk menghindari race condition saldo saat dibaca
            $user = User::lockForUpdate()->find($request->user()->id);

            if ($user->saldo < $request->jumlah_nominal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi untuk penarikan ini.'
                ], 400);
            }

            // 1. Amankan saldo nasabah langsung di awal (Potong langsung)
            $user->decrement('saldo', $request->jumlah_nominal);

            // 2. Buat data request tarik tunai
            $tarikTunai = TarikTunai::create([
                'user_id' => $user->id,
                'jumlah_nominal' => $request->jumlah_nominal,
                'status' => 'pending',
                'tanggal_request' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request penarikan berhasil dibuat. Saldo Anda telah diamankan sementara.',
                'data' => $tarikTunai
            ], 201);
        });
    }

    /**
     * Admin menyetujui request penarikan tunai
     */
    public function approve($id)
    {
        $tarikTunai = TarikTunai::findOrFail($id);

        if ($tarikTunai->status !== 'pending') {
            return response()->json(['message' => 'Request sudah diproses sebelumnya.'], 400);
        }

        return DB::transaction(function () use ($tarikTunai) {
            // 1. Update Status Tarik Tunai menjadi approved
            $tarikTunai->update([
                'status' => 'approved',
                'tanggal_selesai' => now(),
            ]);

            // 2. Catat di Mutasi Saldo sebagai transaksi sukses keluar
            MutasiSaldo::create([
                'user_id' => $tarikTunai->user_id,
                'jenis_transaksi' => 'keluar',
                'sumber' => 'tarik_tunai',
                'referensi_id' => $tarikTunai->id,
                'nominal' => $tarikTunai->jumlah_nominal,
                'status' => 'success',
                'keterangan' => 'Penarikan tunai disetujui oleh admin'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Penarikan tunai berhasil disetujui.'
            ]);
        });
    }

    /**
     * Admin/Nasabah membatalkan request (Ada fitur REFUND)
     */
    public function reject($id)
    {
        $tarikTunai = TarikTunai::findOrFail($id);

        if ($tarikTunai->status !== 'pending') {
            return response()->json(['message' => 'Request sudah tidak bisa dibatalkan.'], 400);
        }

        return DB::transaction(function () use ($tarikTunai) {
            // 1. Kembalikan saldo ke user (Refund)
            $user = User::lockForUpdate()->find($tarikTunai->user_id);
            $user->increment('saldo', $tarikTunai->jumlah_nominal);

            // 2. Update status menjadi rejected
            $tarikTunai->update([
                'status' => 'rejected',
                'tanggal_selesai' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request penarikan telah ditolak/dibatalkan. Saldo dikembalikan ke nasabah.'
            ]);
        });
    }
}