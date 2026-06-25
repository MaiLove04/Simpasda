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

        // Jika nasabah, tampilkan miliknya saja
        if ($request->user()->role === 'nasabah') {
            $query->where('user_id', $request->user()->id);
        }

        // Filter status jika ada (misal: pending)
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

        $user = $request->user();

        // Cek saldo
        if ($user->saldo < $request->jumlah_nominal) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi untuk penarikan ini.'
            ], 400);
        }

        $tarikTunai = TarikTunai::create([
            'user_id' => $user->id,
            'jumlah_nominal' => $request->jumlah_nominal,
            'status' => 'pending',
            'tanggal_request' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request penarikan berhasil dibuat. Menunggu konfirmasi admin.',
            'data' => $tarikTunai
        ], 201);
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
            $user = User::findOrFail($tarikTunai->user_id);

            // Re-check saldo saat approve (takutnya sudah dipakai transaksi lain)
            if ($user->saldo < $tarikTunai->jumlah_nominal) {
                return response()->json(['message' => 'Saldo nasabah sudah tidak mencukupi.'], 400);
            }

            // 1. Kurangi Saldo User
            $user->decrement('saldo', $tarikTunai->jumlah_nominal);

            // 2. Update Status Tarik Tunai
            $tarikTunai->update([
                'status' => 'approved',
                'tanggal_selesai' => now(),
            ]);

            // 3. Catat di Mutasi Saldo
            MutasiSaldo::create([
                'user_id' => $user->id,
                'jenis_transaksi' => 'keluar',
                'sumber' => 'tarik_tunai',
                'referensi_id' => $tarikTunai->id,
                'nominal' => $tarikTunai->jumlah_nominal,
                'status' => 'success',
                'keterangan' => 'Penarikan tunai disetujui oleh admin'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Penarikan tunai berhasil disetujui dan saldo telah terpotong.'
            ]);
        });
    }

    /**
     * Admin/Nasabah membatalkan request
     */
    public function reject($id)
    {
        $tarikTunai = TarikTunai::findOrFail($id);

        if ($tarikTunai->status !== 'pending') {
            return response()->json(['message' => 'Request sudah tidak bisa dibatalkan.'], 400);
        }

        $tarikTunai->update([
            'status' => 'rejected',
            'tanggal_selesai' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request penarikan telah ditolak/dibatalkan.'
        ]);
    }
}
