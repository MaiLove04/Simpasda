<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'jadwal_penjemputans',
            function (Blueprint $table) {

                $table->uuid('id')->primary();
                
                // Bank sampah yang membuat jadwal
                $table->foreignId('bank_sampah_id')
                    ->constrained('bank_sampahs')
                    ->cascadeOnDelete();

                // Nasabah yang akan dijemput
                $table->foreignId('nasabah_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                // Kurir yang bertugas
                $table->foreignId('kurir_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                // Tanggal + jam penjemputan
                $table->dateTime(
                    'tanggal_penjemputan'
                );

                // Lokasi penjemputan
                $table->text(
                    'alamat'
                );

                // Catatan tambahan
                $table->text(
                    'catatan'
                )->nullable();

                // Status jadwal
                $table->enum(
                    'status',
                    [
                        'terjadwal',
                        'proses',
                        'selesai',
                        'batal',
                    ]
                )->default(
                    'terjadwal'
                );

                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'jadwal_penjemputans'
        );
    }
};