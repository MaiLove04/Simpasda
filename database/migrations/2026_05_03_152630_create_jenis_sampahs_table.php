<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_sampahs', function (Blueprint $table) {
            $table->id();

            // relasi ke bank sampah
            $table->foreignId('bank_sampah_id')
                  ->constrained('bank_sampahs')
                  ->onDelete('cascade');

            // nama sampah
            $table->string('nama');

            // icon preset untuk Flutter
            $table->enum('kode_icon', [
                'plastik',
                'kertas',
                'logam',
                'organik',
                'kaca',
                'elektronik'
            ]);

            // harga
            $table->decimal('harga_per_kg', 10, 2);

            // aktif/nonaktif
            $table->enum('status', [
                'aktif',
                'nonaktif'
            ])->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_sampahs');
    }
};