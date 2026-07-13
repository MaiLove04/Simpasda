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
        Schema::create('mitras', function (Blueprint $table) {
            $table->id();

            $table->string('nama_mitra');
            $table->enum('jenis_mitra', [
                'Pengepul',
                'Vendor',
                'Instansi',
                'UMKM',
                'Lainnya'
            ]);

            $table->string('penanggung_jawab');

            $table->string('no_hp');

            $table->string('email')->nullable();

            $table->text('alamat');

            $table->enum('status', [
                'Aktif',
                'Tidak Aktif'
            ])->default('Aktif');

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitras');
    }
};
