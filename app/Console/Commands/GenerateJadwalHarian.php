<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterJadwalRutin;
use App\Models\JadwalPenjemputan;
use Carbon\Carbon;

class GenerateJadwalHarian extends Command
{
    protected $signature = 'jadwal:generate-harian';

    protected $description = 'Otomatis generate jadwal penjemputan harian berdasarkan master data rutin';

    public function handle()
    {
        // 1. Dapatkan nama hari besok dalam bahasa Indonesia (Contoh: "Senin", "Selasa", dll)
        $hariBesok = Carbon::tomorrow()->locale('id')->isoFormat('dddd');
        $tanggalBesokYmd = Carbon::tomorrow()->toDateString(); // Format: YYYY-MM-DD untuk cek duplikat

        $this->info("Memulai generate jadwal untuk hari: $hariBesok ($tanggalBesokYmd)...");

        // 2. Ambil semua pola master rutin yang aktif untuk hari besok beserta relasi nasabahnya
        $polaRutins = MasterJadwalRutin::with('nasabah') 
                                        ->where('hari_penjemputan', $hariBesok)
                                        ->where('is_aktif', true)
                                        ->get();

        if ($polaRutins->isEmpty()) {
            $this->info("Tidak ada pola jadwal rutin untuk hari $hariBesok.");
            return Command::SUCCESS;
        }

        $counter = 0;

        foreach ($polaRutins as $pola) {
            // PENGAMAN: Cek apakah jadwal harian untuk nasabah dan kurir ini di tanggal besok sudah pernah dibuat?
            $sudahAda = JadwalPenjemputan::where('nasabah_id', $pola->nasabah_id)
                                         ->where('kurir_id', $pola->kurir_id)
                                         ->whereDate('tanggal_penjemputan', $tanggalBesokYmd)
                                         ->exists();

            if ($sudahAda) {
                $this->comment("Jadwal untuk Nasabah ID {$pola->nasabah_id} besok sudah ada. Dilewati.");
                continue; // Lanjut ke data pola berikutnya tanpa memasukkan data baru
            }

            // Gabungkan tanggal besok dengan jam estimasi dari master pola
            $waktuPenjemputan = Carbon::tomorrow()->setTimeFromTimeString($pola->jam_estimasi);

            // 3. Masukkan datanya ke dalam tabel jadwal transaksi harian
            // 3. Masukkan datanya ke dalam tabel jadwal transaksi harian
            JadwalPenjemputan::create([
                'nasabah_id'          => $pola->nasabah_id,
                'kurir_id'            => $pola->kurir_id,
                
                'bank_sampah_id'      => $pola->bank_sampah_id?? $pola->nasabah->bank_sampah_id?? 1, // Coba ambil dari pola dulu, kalau kosong baru ambil dari relasi nasabah 
                
                'alamat'              => $pola->nasabah->alamat ?? '-',
                'tanggal_penjemputan' => $waktuPenjemputan,
                'status'              => 'terjadwal', 
            ]);

            $counter++;
        }

        $this->info("Sukses generate $counter jadwal penjemputan untuk hari besok!");
        return Command::SUCCESS;
    }
}