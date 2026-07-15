<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Mengambil notifikasi untuk user yang sedang login (Kurir).
     * Endpoint ini dipanggil dari rute /notifikasi-kurir.
     */
    public function getNotifikasiKurir()
    {
        return $this->getNotifikasi();
    }

    /**
     * Mengambil notifikasi untuk user yang sedang login (Nasabah).
     * Endpoint ini dipanggil dari rute /notifikasi-nasabah.
     */
    public function getNotifikasiNasabah()
    {
        return $this->getNotifikasi();
    }

    /**
     * Logic utama untuk mengambil notifikasi berdasarkan user yang sedang login.
     * Metode ini bersifat private dan digunakan oleh dua metode publik di atas.
     */
    private function getNotifikasi()
    {
        try {
            $user = Auth::user();

            // Mengambil semua notifikasi untuk user tersebut, diurutkan dari yang terbaru.
            $notifikasi = Notifikasi::where('user_id', $user->id)
                                    ->latest()
                                    ->get();

            // Menghitung jumlah notifikasi yang belum dibaca.
            // OPTIMASI: Hitung dari koleksi yang sudah diambil untuk menghindari query tambahan ke database.
            $unreadCount = $notifikasi->whereNull('read_at')->count();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
                'data' => $notifikasi
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menandai notifikasi spesifik sebagai sudah dibaca.
     *
     * @param int $id ID dari notifikasi
     */
    public function markAsRead($id)
    {
        try {
            // Cari notifikasi berdasarkan ID dan pastikan milik user yang sedang login.
            $notifikasi = Notifikasi::where('id', $id)
                                    ->where('user_id', Auth::id())
                                    ->first();

            if (!$notifikasi) {
                return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan atau Anda tidak memiliki akses.'], 404);
            }

            // Jika notifikasi belum dibaca (read_at masih null), update dengan waktu sekarang.
            if (is_null($notifikasi->read_at)) {
                $notifikasi->read_at = now();
                $notifikasi->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil ditandai sebagai dibaca.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses permintaan: ' . $e->getMessage()
            ], 500);
        }
    }
}