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
        Schema::table('master_jadwal_rutins', function (Blueprint $table) {
            // Menambahkan kolom tanggal_penjemputan_berikutnya setelah kolom tanggal_mulai
            $table->date('tanggal_penjemputan_berikutnya')->nullable()->after('tanggal_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_jadwal_rutins', function (Blueprint $table) {
            // Menghapus kolom jika migration di-rollback
            $table->dropColumn('tanggal_penjemputan_berikutnya');
        });
    }
};