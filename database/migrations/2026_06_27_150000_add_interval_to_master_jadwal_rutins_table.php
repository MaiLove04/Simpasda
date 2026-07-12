<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_jadwal_rutins', function (Blueprint $table) {
            // Tipe jadwal: 'mingguan' (default, backward compatible) atau 'interval'
            $table->string('tipe_jadwal')->default('mingguan')->after('is_aktif');

            // Jumlah hari interval (contoh: 2 = setiap 2 hari sekali)
            $table->unsignedInteger('interval_hari')->nullable()->after('tipe_jadwal');

            // Tanggal awal untuk menghitung siklus interval
            $table->date('tanggal_mulai')->nullable()->after('interval_hari');

            // Ubah hari_penjemputan jadi nullable (karena tipe interval tidak butuh hari)
            $table->string('hari_penjemputan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('master_jadwal_rutins', function (Blueprint $table) {
            $table->dropColumn(['tipe_jadwal', 'interval_hari', 'tanggal_mulai']);
            $table->string('hari_penjemputan')->nullable(false)->change();
        });
    }
};
