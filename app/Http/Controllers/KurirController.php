<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JadwalPenjemputan;
use App\Models\SetorSampah; // 
use Carbon\Carbon;

class KurirController extends Controller
{
    public function dashboard_kurir($id)
    {
        $user = User::find($id);
        
        // 1. Ambil jadwal penjemputan khusus untuk HARI INI
        $jadwalHariIni = JadwalPenjemputan::where('kurir_id', $id)
            ->whereDate('tanggal_penjemputan', Carbon::today())
            ->latest()
            ->first();

        // 2. Hitung total lokasi/tugas untuk HARI INI berdasarkan jadwal yang aktif
        // Jika jadwal harian kosong, nilainya otomatis 0
        $totalPesanan = JadwalPenjemputan::where('kurir_id', $id)
            ->whereDate('tanggal_penjemputan', Carbon::today())
            ->count();

        // 3. Hitung tugas yang SUDAH SELESAI hari ini (status selesai)
        $totalPesananSelesai = JadwalPenjemputan::where('kurir_id', $id)
            ->whereDate('tanggal_penjemputan', Carbon::today())
            ->where('status', 'selesai') // 🔥 Menghitung persentase progress bar di Flutter
            ->count();

        // 4. Hitung ringkasan hasil timbangan berat & total rupiah khusus HARI INI
        // Asumsi data transaksi tersimpan di tabel setor_sampah / setoran yang mencatat kurir_id
        $totalBeratHariIni = SetorSampah::where('kurir_id', $id)
            ->whereDate('created_at', Carbon::today())
            ->sum('berat'); // Sesuaikan nama kolom berat di database kamu

        $totalPendapatanHariIni = SetorSampah::where('kurir_id', $id)
            ->whereDate('created_at', Carbon::today())
            ->sum('total'); // Sesuaikan nama kolom total harga/rupiah di database kamu

        // 5. Hitung performa bulanan (Catatan Performa)
        $beratBulanIni = SetorSampah::where('kurir_id', $id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('berat');

        return response()->json([
            'nama_kurir'                => $user->name ?? 'Kurir',
            'foto'                      => $user->foto ?? null,
            'email'                     => $user->email ?? '-',
            'no_hp'                     => $user->no_hp ?? '-',
            'alamat'                    => $user->alamat ?? '-',
            
            // Variabel sinkronisasi untuk Kartu Misi & Ringkasan Beranda
            'total_pesanan'             => $totalPesanan,
            'total_pesanan_selesai'     => $totalPesananSelesai,
            'total_berat_hari_ini'      => $totalBeratHariIni ?? 0,
            'total_pendapatan_hari_ini' => number_format($totalPendapatanHariIni, 0, ',', '.'), // Format rupiah otomatis
            'berat_bulan_ini'           => $beratBulanIni ?? 0,
            'keterangan_tren'           => 'Tetap semangat menjaga kebersihan lingkungan bersama ASRI.',
            
            'jadwal'                    => $jadwalHariIni,
            'aktivitas_terbaru'         => [] // Bisa diisi kueri data setor terakhir jika diperlukan
        ]);
    }
}