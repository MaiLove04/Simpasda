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
     * ==========================================
     * 🌐 FUNGSI WEB ADMIN (CRUD KURIR)
     * ==========================================
     */
    public function index()
    {
        $kurirs = User::where('role', 'kurir')->get();
        return response()->json(['status' => 'success', 'data' => $kurirs], 200);
    }

    public function show($id)
    {
        $kurir = User::where('role', 'kurir')->find($id);
        if (!$kurir) {
            return response()->json(['status' => 'error', 'message' => 'Kurir tidak ditemukan'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $kurir], 200);
    }

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
            'kode_nasabah' => $kodeKurir,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Data kurir berhasil ditambahkan', 'data' => $kurir], 201);
    }

    public function update(Request $request, $id)
    {
        $kurir = User::where('role', 'kurir')->find($id);
        if (!$kurir) return response()->json(['status' => 'error', 'message' => 'Kurir tidak ditemukan'], 404);

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

        if ($request->filled('password')) $kurir->update(['password' => bcrypt($request->password)]);

        return response()->json(['status' => 'success', 'message' => 'Data kurir berhasil diupdate', 'data' => $kurir], 200);
    }

    public function destroy($id)
    {
        $kurir = User::where('role', 'kurir')->find($id);
        if (!$kurir) return response()->json(['status' => 'error', 'message' => 'Kurir tidak ditemukan'], 404);
        
        $kurir->delete();
        return response()->json(['status' => 'success', 'message' => 'Data kurir berhasil dihapus'], 200);
    }

    public function getDashboardStats()
    {
        try {
            return response()->json([
                'total_nasabah' => User::where('role', 'nasabah')->count(),
                'total_kurir' => User::where('role', 'kurir')->count(),
                'total_transaksi' => SetorSampah::count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['total_nasabah' => 0, 'total_kurir' => 0, 'total_transaksi' => 0, 'error' => $e->getMessage()], 200);
        }
    }

    /**
     * ==========================================
     * 📱 FUNGSI APLIKASI FLUTTER (MOBILE)
     * ==========================================
     */
    public function dashboard_kurir($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'Kurir tidak ditemukan'], 404);

        $jadwalHariIni = JadwalPenjemputan::with(['nasabah', 'bankSampah'])
            ->where('kurir_id', $id)->whereDate('tanggal_penjemputan', Carbon::today())
            ->where('status', '!=', 'selesai')->orderBy('tanggal_penjemputan', 'asc')->first();

        $totalJadwalAktif = JadwalPenjemputan::where('kurir_id', $id)->whereDate('tanggal_penjemputan', Carbon::today())
            ->whereIn('status', ['terjadwal', 'proses'])->count();

        $totalRequestPending = SetorSampah::whereNull('kurir_id')->whereNull('jadwal_id')->where('status', 'pending')
            ->when($user->bank_sampah_id, function ($q) use ($user) {
                $q->whereHas('nasabah', function ($q2) use ($user) {
                    $q2->where('bank_sampah_id', $user->bank_sampah_id);
                });
            })->count();

        $totalTugasAktif = $totalJadwalAktif + $totalRequestPending;
        $totalPesananSelesai = JadwalPenjemputan::where('kurir_id', $id)->whereDate('tanggal_penjemputan', Carbon::today())->where('status', 'selesai')->count();

        $setorHariIniIds = SetorSampah::where('kurir_id', $id)->whereDate('created_at', Carbon::today())->pluck('id');
        $totalBeratHariIni = DetailSetorSampah::whereIn('setor_sampah_id', $setorHariIniIds)->sum('berat');
        $totalPendapatanHariIni = SetorSampah::whereIn('id', $setorHariIniIds)->sum('total');

        $setorBulanIniIds = SetorSampah::where('kurir_id', $id)->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->pluck('id');
        $beratBulanIni = DetailSetorSampah::whereIn('setor_sampah_id', $setorBulanIniIds)->sum('berat');

        $aktivitasTerbaru = SetorSampah::with('nasabah')->where('kurir_id', $id)->whereDate('created_at', Carbon::today())
            ->latest()->take(3)->get()->map(function($item) {
                $beratTransaksi = DetailSetorSampah::where('setor_sampah_id', $item->id)->sum('berat');
                $detailPertama = DetailSetorSampah::with('jenisSampah')->where('setor_sampah_id', $item->id)->first();
                $namaJenis = $detailPertama && $detailPertama->jenisSampah ? $detailPertama->jenisSampah->nama : 'Sampah';

                $totalItem = DetailSetorSampah::where('setor_sampah_id', $item->id)->count();
                if ($totalItem > 1) $namaJenis .= ' + ' . ($totalItem - 1) . ' lainnya';

                return [
                    'id' => $item->id,
                    'total' => $item->total,
                    'berat' => round($beratTransaksi, 1),
                    'created_at_formatted' => Carbon::parse($item->created_at)->format('H:i') . ' WIB',
                    'jenis_sampah' => ['nama' => $namaJenis]
                ];
            });

        return response()->json([
            'nama_kurir'                => $user->name ?? 'Kurir',
            'foto'                      => $user->foto ?? null,
            'email'                     => $user->email ?? '-',
            'no_hp'                     => $user->no_hp ?? '-',
            'alamat'                    => $user->alamat ?? '-',
            'total_tugas_aktif'         => $totalTugasAktif,
            'total_pesanan'             => $totalTugasAktif,
            'total_request_pending'     => $totalRequestPending,
            'total_jadwal_aktif'        => $totalJadwalAktif,
            'total_pesanan_selesai'     => $totalPesananSelesai,
            'total_berat_hari_ini'      => round($totalBeratHariIni, 1),
            'total_pendapatan_hari_ini' => number_format($totalPendapatanHariIni, 0, ',', '.'),
            'berat_bulan_ini'           => round($beratBulanIni, 1),
            'keterangan_tren'           => 'Performa kerja Anda luar biasa hari ini, tingkatkan terus!',
            'jadwal'                    => $jadwalHariIni,
            'aktivitas_terbaru'         => $aktivitasTerbaru
        ]);
    }

    public function scanQrNasabah(Request $request)
    {
        $request->validate(['nasabah_id' => 'required', 'kurir_id' => 'required']);

        try {
            $nasabah = User::where('role', 'nasabah')
                ->where(function ($q) use ($request) {
                    $q->where('id', $request->nasabah_id)->orWhere('kode_nasabah', $request->nasabah_id);
                })->first();

            if (!$nasabah) return response()->json(['status' => 'error', 'message' => 'QR Code tidak valid.'], 404);

            $requestAktif = SetorSampah::with('details.jenisSampah')->where('user_id', $nasabah->id)
                ->whereNull('jadwal_id')->where('status', 'pending')->latest()->first();

            if ($requestAktif) {
                return response()->json([
                    'status' => 'success', 'mode' => 'request', 'jadwal_id' => null, 'setor_sampah_id' => $requestAktif->id,
                    'nasabah' => ['id' => $nasabah->id, 'nama' => $nasabah->name, 'alamat' => $nasabah->alamat]
                ], 200);
            }

            $jadwal = JadwalPenjemputan::where('nasabah_id', $nasabah->id)->where('kurir_id', $request->kurir_id)
                ->whereIn('status', ['terjadwal', 'proses'])->latest()->first();

            if ($jadwal) {
                return response()->json([
                    'status' => 'success', 'mode' => 'jadwal', 'jadwal_id' => $jadwal->id, 'setor_sampah_id' => null,
                    'nasabah' => ['id' => $nasabah->id, 'nama' => $nasabah->name, 'alamat' => $nasabah->alamat]
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Nasabah tidak memiliki tugas penjemputan aktif.'], 404);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}