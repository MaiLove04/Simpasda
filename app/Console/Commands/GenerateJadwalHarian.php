<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterJadwalRutin;
use App\Models\JadwalPenjemputan;
use Carbon\Carbon;

class GenerateJadwalHarian extends Command
{
    protected $signature = 'jadwal:generate-harian {--today : Generate untuk hari ini}';

    protected $description = 'Otomatis generate jadwal penjemputan harian berdasarkan master data rutin (mingguan & interval)';

    public function handle()
    {
        // 🔥 MODIFIKASI: Gunakan opsi --today jika ada, jika tidak tanya interaktif (jika di terminal)
        $isToday = $this->option('today');

        if (!$isToday && PHP_SAPI === 'cli' && $this->confirm('Apakah Anda ingin generate untuk HARI INI? (Default: BESOK)', false)) {
            $isToday = true;
        }

        $targetDate = $isToday ? Carbon::today() : Carbon::tomorrow();

        // 🔥 FIX LOCALE: Pemetaan hari manual agar tidak tergantung bahasa server
        $daftarHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $hariTarget = $daftarHari[$targetDate->format('l')];
        $tanggalTargetYmd = $targetDate->toDateString();

        $this->info("Memulai generate jadwal untuk hari: $hariTarget ($tanggalTargetYmd)...");

        // ============================
        // 1. JADWAL MINGGUAN
        // ============================
        $polaMingguan = MasterJadwalRutin::with('nasabah')
            ->where('tipe_jadwal', 'mingguan')
            ->where('hari_penjemputan', $hariTarget)
            ->where('is_aktif', true)
            ->get();

        // ============================
        // 2. JADWAL INTERVAL
        // ============================
        $polaIntervalSemua = MasterJadwalRutin::with('nasabah')
            ->where('tipe_jadwal', 'interval')
            ->where('is_aktif', true)
            ->whereNotNull('interval_hari')
            ->whereNotNull('tanggal_mulai')
            ->get();

        // Filter: hanya ambil pola yang intervalnya cocok
        $polaInterval = $polaIntervalSemua->filter(function ($pola) use ($targetDate) {
            $tanggalMulai = Carbon::parse($pola->tanggal_mulai);
            $selisihHari = $tanggalMulai->diffInDays($targetDate);
            return $selisihHari % $pola->interval_hari === 0;
        });

        // Gabungkan kedua koleksi
        $semuaPola = $polaMingguan->merge($polaInterval);

        if ($semuaPola->isEmpty()) {
            $this->info("Tidak ada pola jadwal rutin yang cocok untuk hari $hariTarget.");
            return Command::SUCCESS;
        }

        $counter = 0;

        foreach ($semuaPola as $pola) {
            // 🔥 PAKSA UPDATE: Hapus data jadwal yang sudah ada untuk nasabah ini di hari ini/besok
            // Agar tidak ada pesan "Dilewati karena sudah ada"
            JadwalPenjemputan::where('nasabah_id', $pola->nasabah_id)
                             ->whereDate('tanggal_penjemputan', $tanggalTargetYmd)
                             ->delete();

            // Gabungkan tanggal dengan jam estimasi
            $waktuPenjemputan = $targetDate->copy()->setTimeFromTimeString($pola->jam_estimasi);

            // Masukkan ke tabel transaksi harian
            JadwalPenjemputan::create([
                'nasabah_id'          => $pola->nasabah_id,
                'kurir_id'            => $pola->kurir_id,
                'bank_sampah_id'      => $pola->nasabah->bank_sampah_id ?? 1,
                'alamat'              => $pola->nasabah->alamat ?? '-',
                'tanggal_penjemputan' => $waktuPenjemputan,
                'tanggal'             => $tanggalTargetYmd,
                'status'              => 'terjadwal',
            ]);

            $counter++;
        }

        $this->info("Sukses generate $counter jadwal penjemputan!");
        return Command::SUCCESS;
    }
}
