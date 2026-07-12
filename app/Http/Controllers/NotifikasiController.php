<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Notifikasi;

class NotifikasiController extends Controller
{
    /**
     * Ambil list notifikasi untuk kurir
     */
    public function getNotifikasiKurir($userId)
    {
        $notifications = Notifikasi::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications, 200);
    }

    /**
     * Ambil list notifikasi untuk nasabah
     */
    public function getNotifikasiNasabah($userId)
    {
        $notifications = Notifikasi::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications, 200);
    }

    /**
     * Tandai notifikasi sebagai telah dibaca
     */
    public function markAsRead($id)
    {
        $notification = Notifikasi::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai sebagai telah dibaca'
        ], 200);
    }
}
