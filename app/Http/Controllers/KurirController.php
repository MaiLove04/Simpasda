<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JadwalPenjemputan;
use App\Models\SetorSampah; 
use App\Models\DetailSetorSampah; // 
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
        $totalPesanan = JadwalPenjemputan::where('kurir_id', $id)
            ->whereDate('tanggal_penjemputan', Carbon::today())
            ->count();

        // 3. Hitung tugas yang SUDAH SELESAI hari ini (status selesai)
        $totalPesananSelesai = JadwalPenjemputan::where('kurir_id', $id)
            ->whereDate('tanggal_penjemputan', Carbon::today())
            ->where('status', 'selesai') 
            ->count();

        // 4. Ambil semua ID transaksi setor sampah milik kurir ini KHUSUS HARI INI
        $setorHariIniIds = SetorSampah::where('kurir_id', $id)
            ->whereDate('created_at', Carbon::today())
            ->pluck('id');

        // 🔥 FIX UTAMA: Hitung akumulasi BERAT total dari tabel ANAK (detail_setor_sampahs)
        $totalBeratHariIni = DetailSetorSampah::whereIn('setor_sampah_id', $setorHariIniIds)
            ->sum('berat');

        // Hitung total rupiah pendapatan/perputaran uang dari transaksi hari ini
        $totalPendapatanHariIni = SetorSampah::whereIn('id', $setorHariIniIds)
            ->sum('total');

        // 5. Hitung performa bulanan (Catatan Performa)
        $setorBulanIniIds = SetorSampah::where('kurir_id', $id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->pluck('id');

        // 🔥 FIX KEDUA: Hitung akumulasi berat bulanan dari tabel anak juga
        $beratBulanIni = DetailSetorSampah::whereIn('setor_sampah_id', $setorBulanIniIds)
            ->sum('berat');

        // 6. AMBIL AKTIVITAS TERBARU (Agar list di bawah dashboard tidak kosong melompong)
        $aktivitasTerbaru = SetorSampah::with('nasabah')
            ->where('kurir_id', $id)
            ->whereDate('created_at', Carbon::today())
            ->latest()
            ->take(3)
            ->get()
            ->map(function($item) {
                // Hitung berat total per transaksi dari tabel anak
                $beratTransaksi = DetailSetorSampah::where('setor_sampah_id', $item->id)->sum('berat');
                
                // Cek detail item untuk menampilkan nama jenis sampah pertama
                $detailPertama = DetailSetorSampah::with('jenisSampah')
                    ->where('setor_sampah_id', $item->id)
                    ->first();
                
                $namaJenis = $detailPertama && $detailPertama->jenisSampah 
                    ? $detailPertama->jenisSampah->nama 
                    : 'Sampah';

                // Hitung jika ada item tambahan
                $totalItem = DetailSetorSampah::where('setor_sampah_id', $item->id)->count();
                if ($totalItem > 1) {
                    $namaJenis .= ' + ' . ($totalItem - 1) . ' lainnya';
                }

                return [
                    'id' => $item->id,
                    'total' => $item->total,
                    'berat' => round($beratTransaksi, 1),
                    'created_at_formatted' => Carbon::parse($item->created_at)->format('H:i') . ' WIB',
                    'jenis_sampah' => [
                        'nama' => $namaJenis
                    ]
                ];
            });

        return response()->json([
            'nama_kurir'                => $user->name ?? 'Kurir',
            'foto'                      => $user->foto ?? null,
            'email'                     => $user->email ?? '-',
            'no_hp'                     => $user->no_hp ?? '-',
            'alamat'                    => $user->alamat ?? '-',
            
            // Pasokan variabel untuk sinkronisasi Flutter Dashboard kamu
            'total_pesanan'             => $totalPesanan,
            'total_pesanan_selesai'     => $totalPesananSelesai,
            'total_berat_hari_ini'      => round($totalBeratHariIni, 1), 
            'total_pendapatan_hari_ini' => number_format($totalPendapatanHariIni, 0, ',', '.'), 
            'berat_bulan_ini'           => round($beratBulanIni, 1),
            'keterangan_tren'           => 'Performa kerja Anda luar biasa hari ini, tingkatkan terus!',
            
            'jadwal'                    => $jadwalHariIni,
            'aktivitas_terbaru'         => $aktivitasTerbaru
        ]);
    }
}