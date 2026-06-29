<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JadwalPenjemputan;
use App\Models\SetorSampah;
use App\Models\DetailSetorSampah;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KurirController extends Controller
{
    /**
     * TAMPILAN WEB ADMIN: List semua data kurir lapangan
     */
    public function index()
    {
        // Mengambil semua user dengan role kurir
        $kurirs = User::where('role', 'kurir')->get();

        return response()->json([
            'status' => 'success',
            'data' => $kurirs
        ], 200);
    }

    // =========================================================================
    // API: TAMBAH KURIR BARU
    // =========================================================================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'alamat' => 'required',
            'no_hp' => 'required|unique:users',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bank_sampah_id' => 'required|exists:bank_sampahs,id',
        ]);

        $foto = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/users'), $fileName);
            $foto = 'uploads/users/' . $fileName;
        }

        // Hitung kode kurir otomatis secara dinamis
        $jumlahKurir = User::where('role', 'kurir')->count() + 1;
        $kodeKurir = 'KRR' . str_pad($jumlahKurir, 3, '0', STR_PAD_LEFT);

        $kurir = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'foto' => $foto,
            'role' => 'kurir',
            'status' => 'aktif',
            'bank_sampah_id' => $request->bank_sampah_id,
            'kode_nasabah' => $kodeKurir, // Menggunakan kolom yang sama agar tidak merusak database schema
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data kurir berhasil ditambahkan',
            'data' => $kurir
        ], 201);
    }

    // =========================================================================
    // API: EDIT DATA KURIR
    // =========================================================================
    public function update(Request $request, $id)
    {
        $kurir = User::where('role', 'kurir')->find($id);

        if (!$kurir) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kurir tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $kurir->id,
            'alamat' => 'required',
            'no_hp' => 'required|unique:users,no_hp,' . $kurir->id,
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $foto = $kurir->foto;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/users'), $fileName);
            $foto = 'uploads/users/' . $fileName;
        }

        $kurir->update([
            'name' => $request->name,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'foto' => $foto,
        ]);

        // Update password hanya jika form password diisi
        if ($request->filled('password')) {
            $kurir->update(['password' => bcrypt($request->password)]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data kurir berhasil diupdate',
            'data' => $kurir
        ], 200);
    }

    // =========================================================================
    // API: HAPUS DATA KURIR
    // =========================================================================
    public function destroy($id)
    {
        $kurir = User::where('role', 'kurir')->find($id);

        if (!$kurir) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kurir tidak ditemukan'
            ], 404);
        }

        $kurir->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data kurir berhasil dihapus'
        ], 200);
    }

    /**
     * TAMPILAN WEB ADMIN: Mengambil counter statistik untuk dashboard.html
     */
    public function getDashboardStats()
    {
        try {
            $totalNasabah = User::where('role', 'nasabah')->count();
            $totalKurir = User::where('role', 'kurir')->count();

            // Menghitung akumulasi transaksi dari tabel SetorSampah
            $totalTransaksi = SetorSampah::count();

            return response()->json([
                'total_nasabah' => $totalNasabah,
                'total_kurir' => $totalKurir,
                'total_transaksi' => $totalTransaksi
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'total_nasabah' => 0,
                'total_kurir' => 0,
                'total_transaksi' => 0,
                'error' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * APLIKASI FLUTTER: Dashboard spesifik untuk tiap kurir
     */
    public function dashboard_kurir($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Kurir tidak ditemukan'], 404);
        }

        // 1. Ambil jadwal penjemputan khusus untuk HARI INI
        $jadwalHariIni = JadwalPenjemputan::with(['nasabah', 'bankSampah'])
            ->where('kurir_id', $id)
            ->whereDate('tanggal_penjemputan', Carbon::today())
            ->where('status', '!=', 'selesai')
            ->orderBy('tanggal_penjemputan', 'asc') // Ambil yang jam-nya paling awal
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
                    ?->where('setor_sampah_id', $item->id)
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
            'total_pesanan_selesan'     => $totalPesananSelesai,
            'total_berat_hari_ini'      => round($totalBeratHariIni, 1),
            'total_pendapatan_hari_ini' => number_format($totalPendapatanHariIni, 0, ',', '.'),
            'berat_bulan_ini'           => round($beratBulanIni, 1),
            'keterangan_tren'           => 'Performa kerja Anda luar biasa hari ini, tingkatkan terus!',

            'jadwal'                    => $jadwalHariIni,
            'aktivitas_terbaru'         => $aktivitasTerbaru
        ]);
    }
}
