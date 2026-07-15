<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TarikTunai;
use App\Models\User;
use App\Models\MutasiSaldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarikTunaiController extends Controller
{
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

    public function approve($id)
    {
        $tarikTunai = TarikTunai::findOrFail($id);

        if ($tarikTunai->status !== 'pending') {
            return response()->json(['message' => 'Request sudah diproses sebelumnya.'], 400);
        }

        return DB::transaction(function () use ($tarikTunai) {
            // 1. Amankan dan potong saldo user di sini (KARENA BELUM DIPOTONG DI AWAL)
            $user = User::lockForUpdate()->find($tarikTunai->user_id);
            
            if ($user->saldo < $tarikTunai->jumlah_nominal) {
                return response()->json(['message' => 'Saldo nasabah sudah tidak mencukupi saat ini.'], 400);
            }
            // 👉 INI YANG PENTING: Saldo dipotong saat di-approve
            $user->decrement('saldo', $tarikTunai->jumlah_nominal);

            // 2. Update Status Tarik Tunai menjadi approved
            $tarikTunai->update([
                'status' => 'approved',
                'tanggal_selesai' => now(),
            ]);

            // 3. Update status Mutasi Saldo yang pending menjadi success
            MutasiSaldo::where('user_id', $tarikTunai->user_id)
                ->where('sumber', 'tarik_tunai')
                ->where('nominal', $tarikTunai->jumlah_nominal)
                ->where('status', 'pending')
                ->update(['status' => 'success']);

            return response()->json([
                'success' => true,
                'message' => 'Penarikan tunai disetujui. Saldo nasabah telah dipotong.'
            ]);
        });
    }

    public function reject($id)
    {
        $tarikTunai = TarikTunai::findOrFail($id);

        if ($tarikTunai->status !== 'pending') {
            return response()->json(['message' => 'Request sudah tidak bisa dibatalkan.'], 400);
        }

        return DB::transaction(function () use ($tarikTunai) {
            // 👉 LOGIKA REFUND DIHAPUS. Tidak ada penambahan saldo karena memang belum dipotong.
            
            // 1. Update status menjadi rejected
            $tarikTunai->update([
                'status' => 'rejected',
                'tanggal_selesai' => now(),
            ]);

            // 2. Update status Mutasi Saldo yang pending menjadi rejected
            MutasiSaldo::where('user_id', $tarikTunai->user_id)
                ->where('sumber', 'tarik_tunai')
                ->where('nominal', $tarikTunai->jumlah_nominal)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);

            return response()->json([
                'success' => true,
                'message' => 'Request penarikan ditolak/dibatalkan.'
            ]);
        });
    }
}