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
            $table->uuid('jadwal_id')->nullable();

            $table->foreign('jadwal_id')
                ->references('id')
                ->on('jadwal_penjemputans')
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
