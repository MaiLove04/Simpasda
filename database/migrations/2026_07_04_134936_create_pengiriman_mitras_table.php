<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengiriman_mitras', function (Blueprint $table) {
            $table->id();

            $table->string('kode_pengiriman');

            $table->foreignId('mitra_id')
                ->constrained('mitras')
                ->onDelete('cascade');

            $table->date('tanggal');

            $table->decimal('total', 15, 2)->default(0);

            $table->enum('status_pengiriman', [
                'Menunggu Mitra',
                'Diterima',
                'Selesai'
            ])->default('Menunggu Mitra');

            $table->enum('status_pembayaran', [
                'Belum Bayar',
                'Menunggu Verifikasi',
                'Lunas'
            ])->default('Belum Bayar');

            $table->enum('metode_pembayaran', [
                'Cash',
                'Transfer'
            ])->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengiriman_mitras');
    }
};