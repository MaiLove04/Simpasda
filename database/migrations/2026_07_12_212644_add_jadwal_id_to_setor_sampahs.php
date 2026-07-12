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
        Schema::table('setor_sampahs', function (Blueprint $table) {
            $table->foreignId('jadwal_id')
                  ->nullable()
                  ->after('jenis_sampah_id')
                  ->constrained('jadwal_id_setor_sampahs')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setor_sampahs', function (Blueprint $table) {
            $table->dropForeign(['jadwal_id']);
            $table->dropColumn('jadwal_id');
        });
    }
};
