<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'setor_sampahs',
            function (Blueprint $table) {

                // hapus kolom lama
                $table->dropColumn(
                    'jenis_sampah'
                );


                // tambah relasi baru
                $table->foreignId(
                    'jenis_sampah_id'
                )->after(
                    'kurir_id'
                )->constrained(
                    'jenis_sampahs'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::table(
            'setor_sampahs',
            function (Blueprint $table) {

                $table->dropForeign([
                    'jenis_sampah_id'
                ]);


                $table->dropColumn(
                    'jenis_sampah_id'
                );


                $table->string(
                    'jenis_sampah'
                );
            }
        );
    }
};