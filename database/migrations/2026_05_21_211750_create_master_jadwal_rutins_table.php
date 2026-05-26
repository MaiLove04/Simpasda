<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_jadwal_rutins', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel users (role: nasabah)
            $table->foreignId('nasabah_id')->constrained('users')->onDelete('cascade');
            // Menghubungkan ke tabel users (role: kurir)
            $table->foreignId('kurir_id')->constrained('users')->onDelete('cascade');
            
            // Menyimpan hari penjemputan (opsi: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu)
            $table->string('hari_penjemputan'); 
            
            // Menyimpan estimasi jam jemput (format: 09:00:00)
            $table->time('jam_estimasi')->default('09:00:00'); 
            
            // Status untuk mengaktifkan atau menonaktifkan langganan rutin nasabah
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_jadwal_rutins');
    }
};