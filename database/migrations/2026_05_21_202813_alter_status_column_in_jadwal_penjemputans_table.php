<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('jadwal_penjemputans', function (Blueprint $table) {
            // Menambahkan 'proses' ke dalam opsi ENUM status
            $table->enum('status', ['terjadwal', 'proses', 'selesai', 'batal'])->default('terjadwal')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_penjemputans', function (Blueprint $table) {
            //
        });
    }
};
