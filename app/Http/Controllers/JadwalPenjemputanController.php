<?php

namespace App\Http\Controllers;

use App\Models\SetorSampah;
use App\Models\JadwalPenjemputan;
use App\Models\User;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalPenjemputanController extends Controller
{
    // SCAN QR (Publik)
    public function scanQr($id)
    {
        $jadwal = JadwalPenjemputan::with(['nasabah', 'kurir', 'bankSampah'])->find($id);
        if (!$jadwal) return response()->json(['success' => false, 'message' => 'QR tidak valid'], 404);
        return response()->json(['success' => true, 'data' => $jadwal]);
    }

    // AMBIL JADWAL KURIR
    public function jadwalKurir()
    {
        try {
            $kurirId = auth()->id();
            $kurir = User::find($kurirId);
            $bankSampahId = $kurir?->bank_sampah_id;

            $jadwalAdmin = JadwalPenjemputan::with(['nasabah', 'kurir', 'bankSampah'])
                ->where('kurir_id', $kurirId)
                ->whereIn('status', ['terjadwal', 'proses'])
                ->get()
                ->map(fn($item) => $this->formatTugas('jadwal', $item));

            $requestNasabah = SetorSampah::with(['nasabah', 'details.jenisSampah'])
                ->whereNull('kurir_id')
                ->whereNull('jadwal_id')
                ->where('status', 'pending')
                ->whereHas('nasabah', fn($q) => $q->where('bank_sampah_id', $bankSampahId))
                ->get()
                ->map(fn($item) => $this->formatTugas('request', $item));

            $semuaTugas = collect(array_merge($jadwalAdmin->toArray(), $requestNasabah->toArray()))
                ->sortBy('tanggal')->values();

            return response()->json(['success' => true, 'total' => $semuaTugas->count(), 'data' => $semuaTugas], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // SIMPAN JADWAL
    public function store(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|exists:users,id',
            'kurir_id' => 'required|exists:users,id',
            'tanggal_penjemputan' => 'required|date',
            'alamat' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $jadwal = JadwalPenjemputan::create([
                'bank_sampah_id' => auth()->user()->bank_sampah_id,
                'nasabah_id' => $request->nasabah_id,
                'kurir_id' => $request->kurir_id,
                'tanggal_penjemputan' => $request->tanggal_penjemputan,
                'alamat' => $request->alamat,
                'catatan' => $request->catatan,
                'status' => 'terjadwal',
            ]);

            // Notifikasi Kurir
            Notifikasi::create([
                'user_id' => $request->kurir_id,
                'judul' => 'Tugas Penjemputan Baru',
                'pesan' => 'Jadwal penjemputan baru pada ' . $request->tanggal_penjemputan,
                'type' => 'penjemputan'
            ]);

            // Notifikasi Nasabah
            Notifikasi::create([
                'user_id' => $request->nasabah_id,
                'judul' => 'Jadwal Penjemputan Dibuat',
                'pesan' => 'Sampah Anda akan dijemput pada ' . $request->tanggal_penjemputan,
                'type' => 'penjemputan'
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $jadwal], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // MULAI JEMPUT
    public function mulaiJemput($id)
    {
        $jadwal = JadwalPenjemputan::where('id', $id)->where('kurir_id', auth()->id())->first();
        if (!$jadwal) return response()->json(['success' => false, 'message' => 'Tugas tidak ditemukan.'], 403);

        $jadwal->update(['status' => 'proses']);

        // Notifikasi ke Nasabah
        Notifikasi::create([
            'user_id' => $jadwal->nasabah_id,
            'judul' => 'Kurir Sedang Menuju Lokasi',
            'pesan' => 'Kurir sedang dalam perjalanan menuju lokasi Anda.',
            'type' => 'penjemputan'
        ]);
        
        return response()->json(['success' => true, 'message' => 'Status diupdate']);
    }

    // BATAL JEMPUT
    public function batalJemput($id)
    {
        $jadwal = JadwalPenjemputan::where('id', $id)->where('kurir_id', auth()->id())->first();
        if (!$jadwal) return response()->json(['success' => false, 'message' => 'Tugas tidak ditemukan.'], 403);

        $jadwal->update(['status' => 'dibatalkan']);

        // Notifikasi ke Nasabah
        Notifikasi::create([
            'user_id' => $jadwal->nasabah_id,
            'judul' => 'Jadwal Dibatalkan',
            'pesan' => 'Jadwal penjemputan Anda telah dibatalkan oleh kurir.',
            'type' => 'penjemputan'
        ]);

        return response()->json(['success' => true, 'message' => 'Jadwal dibatalkan']);
    }

    // JADWAL NASABAH
    public function jadwalNasabah()
    {
        $id = auth()->id();
        return response()->json([
            'success' => true,
            'jadwal_mendatang' => JadwalPenjemputan::where('nasabah_id', $id)->whereIn('status', ['terjadwal', 'proses'])->get(),
            'request_pending' => SetorSampah::where('user_id', $id)->where('status', 'pending')->get(),
            'riwayat' => SetorSampah::where('user_id', $id)->where('status', 'selesai')->take(10)->get()
        ]);
    }

    private function formatTugas($type, $item) {
        return [
            'tipe_tugas' => $type,
            'id' => $item->id,
            'tanggal' => $type == 'jadwal' ? $item->tanggal_penjemputan : $item->created_at,
            'status' => $item->status,
            'nasabah' => $item->nasabah,
            'alamat' => $type == 'jadwal' ? $item->alamat : $item->nasabah?->alamat,
            'catatan' => $item->catatan,
        ];
    }
}
