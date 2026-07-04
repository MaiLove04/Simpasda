<?php

namespace App\Http\Controllers;

use App\Models\MasterJadwalRutin;
use App\Models\JadwalPenjemputan;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MasterJadwalRutinController extends Controller
{
    /**
     * Menampilkan Daftar Master Jadwal
     */
    public function index()
    {
        $masterJadwals = MasterJadwalRutin::with(['nasabah', 'kurir'])->latest()->get();
        return view('admin.master-jadwal.index', compact('masterJadwals'));
    }

    /**
     * Menampilkan Form Tambah Pola Rutin
     */
    public function create()
    {
        $nasabahs = User::where('role', 'nasabah')->get();
        $kurirs = User::where('role', 'kurir')->get();
        $hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return view('admin.master-jadwal.create', compact('nasabahs', 'kurirs', 'hariOptions'));
    }

    /**
     * Menyimpan Data Pola Rutin Baru ke Database
     */
    public function store(Request $request)
    {
        $request->validate([
            'nasabah_id'       => 'required|exists:users,id',
            'kurir_id'         => 'required|exists:users,id',
            'tipe_jadwal'      => 'required|in:mingguan,interval',
            'hari_penjemputan' => 'required_if:tipe_jadwal,mingguan|nullable|string',
            'interval_hari'    => 'required_if:tipe_jadwal,interval|nullable|integer|min:2|max:30',
            'tanggal_mulai'    => 'required_if:tipe_jadwal,interval|nullable|date',
            'jam_estimasi'     => 'required',
        ]);

        $hari = $request->tipe_jadwal === 'mingguan' ? $request->hari_penjemputan : null;
        $interval = $request->tipe_jadwal === 'interval' ? $request->interval_hari : null;
        $tanggalMulai = $request->tipe_jadwal === 'interval' ? $request->tanggal_mulai : null;

        MasterJadwalRutin::create([
            'nasabah_id'       => $request->nasabah_id,
            'kurir_id'         => $request->kurir_id,
            'tipe_jadwal'      => $request->tipe_jadwal,
            'hari_penjemputan' => $hari,
            'interval_hari'    => $interval,
            'tanggal_mulai'    => $tanggalMulai,
            'jam_estimasi'     => $request->jam_estimasi,
            'is_aktif'         => true,
        ]);

        return redirect()->route('master-jadwal.index')->with('success', 'Pola penjemputan rutin berhasil ditambahkan!');
    }

    /**
     * Tampilkan Form Edit Pola Rutin
     */
    public function edit($id)
    {
        $master = MasterJadwalRutin::with(['nasabah', 'kurir'])->findOrFail($id);
        $nasabahs = User::where('role', 'nasabah')->get();
        $kurirs = User::where('role', 'kurir')->get();
        $hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return view('admin.master-jadwal.edit', compact('master', 'nasabahs', 'kurirs', 'hariOptions'));
    }

    /**
     * Simpan Perubahan Pola Rutin
     */
    public function update(Request $request, $id)
    {
        $master = MasterJadwalRutin::findOrFail($id);

        $request->validate([
            'nasabah_id'       => 'required|exists:users,id',
            'kurir_id'         => 'required|exists:users,id',
            'tipe_jadwal'      => 'required|in:mingguan,interval',
            'hari_penjemputan' => 'required_if:tipe_jadwal,mingguan|nullable|string',
            'interval_hari'    => 'required_if:tipe_jadwal,interval|nullable|integer|min:2|max:30',
            'tanggal_mulai'    => 'required_if:tipe_jadwal,interval|nullable|date',
            'jam_estimasi'     => 'required',
        ]);

        $hari         = $request->tipe_jadwal === 'mingguan'  ? $request->hari_penjemputan : null;
        $interval     = $request->tipe_jadwal === 'interval'  ? $request->interval_hari   : null;
        $tanggalMulai = $request->tipe_jadwal === 'interval'  ? $request->tanggal_mulai   : null;

        $master->update([
            'nasabah_id'       => $request->nasabah_id,
            'kurir_id'         => $request->kurir_id,
            'tipe_jadwal'      => $request->tipe_jadwal,
            'hari_penjemputan' => $hari,
            'interval_hari'    => $interval,
            'tanggal_mulai'    => $tanggalMulai,
            'jam_estimasi'     => $request->jam_estimasi,
            'is_aktif'         => $request->boolean('is_aktif'),
        ]);

        return redirect()->route('master-jadwal.index')->with('success', 'Pola penjemputan rutin berhasil diperbarui!');
    }

    /**
     * Menghapus Pola Rutin
     */
    public function destroy($id)
    {
        $master = MasterJadwalRutin::findOrFail($id);
        $master->delete();

        return redirect()->route('master-jadwal.index')->with('success', 'Pola penjemputan rutin berhasil dihapus!');
    }

    /**
     * 🔥 SINKRONISASI JADWAL MANUAL (FORCE SYNC)
     * Fungsi ini menyalin master jadwal yang aktif hari ini ke tabel tugas kurir harian.
     */
    public function generate()
    {
        // 1. Definisikan tanggal hari ini dan nama hari dalam Bahasa Indonesia
        $hariIni = Carbon::now()->locale('id')->dayName; // Mengambil nama hari: 'Senin', 'Selasa', dll.
        $tanggalSekarang = Carbon::today();
        
        // 2. Ambil semua master jadwal yang aktif
        $masterJadwals = MasterJadwalRutin::where('is_aktif', true)->get();
        $jumlahTerbuat = 0;

        foreach ($masterJadwals as $master) {
            $harusDibuat = false;

            // Skenario A: Pola Mingguan
            if ($master->tipe_jadwal === 'mingguan' && $master->hari_penjemputan === $hariIni) {
                $harusDibuat = true;
            } 
            // Skenario B: Pola Interval (Hitung selisih hari dari tanggal_mulai)
            elseif ($master->tipe_jadwal === 'interval' && $master->tanggal_mulai) {
                $tanggalMulai = Carbon::parse($master->tanggal_mulai);
                
                if ($tanggalSekarang->greaterThanOrEqualTo($tanggalMulai)) {
                    $selisihHari = $tanggalSekarang->diffInDays($tanggalMulai);
                    
                    // Jika selisih hari habis dibagi nilai interval, berarti hari ini jadwalnya
                    if ($selisihHari % $master->interval_hari === 0) {
                        $harusDibuat = true;
                    }
                }
            }

            // 3. Eksekusi pembuatan Tugas Harian (JadwalPenjemputan) jika lolos kualifikasi hari ini
            if ($harusDibuat) {
                // Cegah duplikasi: pastikan tugas untuk nasabah ini di tanggal sekarang belum pernah dibuat
                $sudahAda = JadwalPenjemputan::where('nasabah_id', $master->nasabah_id)
                    ->whereDate('tanggal_penjemputan', $tanggalSekarang)
                    ->exists();

                if (!$sudahAda) {
                    JadwalPenjemputan::create([
                        'bank_sampah_id'      => $master->nasabah->bank_sampah_id ?? 1, // Fallback ID jika null
                        'nasabah_id'          => $master->nasabah_id,
                        'kurir_id'            => $master->kurir_id,
                        'tanggal_penjemputan' => $tanggalSekarang->format('Y-m-d'),
                        'alamat'              => $master->nasabah->alamat ?? 'Alamat tidak tertera',
                        'catatan'             => 'Penjemputan Rutin Otomatis Sistem (' . ucfirst($master->tipe_jadwal) . ')',
                        'status'              => 'terjadwal',
                    ]);
                    $jumlahTerbuat++;
                }
            }
        }

        if ($jumlahTerbuat > 0) {
            return redirect()->route('master-jadwal.index')->with('success', "✅ Berhasil menyinkronkan {$jumlahTerbuat} tugas penjemputan harian kurir!");
        }

        return redirect()->route('master-jadwal.index')->with('success', 'ℹ️ Sinkronisasi selesai. Tidak ada jadwal rutin baru yang jatuh tempo hari ini.');
    }
}