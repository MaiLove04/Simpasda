<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aduans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role_pengirim', ['nasabah', 'kurir', 'admin_bank'])->comment('Peran saat mengirim aduan');
            $table->string('kategori_aduan')->comment('Contoh: Saldo, Aplikasi Error, Pelayanan, dll');
            $table->text('isi_aduan');
            $table->string('foto_bukti')->nullable();
            $table->enum('status', ['menunggu', 'diproses', 'selesai'])->default('menunggu');
            $table->text('tanggapan')->nullable()->comment('Balasan dari pihak DLH');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aduans');
    }
};