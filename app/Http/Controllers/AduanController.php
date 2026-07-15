<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AduanController extends Controller
{
    /**
     * Menyimpan aduan baru dari aplikasi mobile
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_pengirim' => 'required|in:nasabah,kurir,admin_bank',
            'kategori_aduan' => 'required|string|max:255',
            'isi_aduan' => 'required|string',
            'foto_bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $pathFoto = null;
            if ($request->hasFile('foto_bukti')) {
                $file = $request->file('foto_bukti');
                $namaFile = time() . '_' . $file->getClientOriginalName();

                // Pastikan direktori tujuan ada
                $tujuan = public_path('uploads/aduan');
                if (!file_exists($tujuan)) {
                    mkdir($tujuan, 0777, true);
                }

                $file->move($tujuan, $namaFile);
                $pathFoto = 'uploads/aduan/' . $namaFile;
            }

            $aduan = Aduan::create([
                'user_id' => $request->user_id,
                'role_pengirim' => $request->role_pengirim,
                'kategori_aduan' => $request->kategori_aduan,
                'isi_aduan' => $request->isi_aduan,
                'foto_bukti' => $pathFoto,
                'status' => 'menunggu',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aduan berhasil dikirim!',
                'data' => $aduan
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim aduan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil riwayat aduan berdasarkan user_id
     */
    public function riwayat($user_id)
    {
        $riwayat = Aduan::where('user_id', $user_id)
            ->latest() // Mengurutkan dari yang terbaru
            ->get();

        return response()->json($riwayat, 200);
    }
}
