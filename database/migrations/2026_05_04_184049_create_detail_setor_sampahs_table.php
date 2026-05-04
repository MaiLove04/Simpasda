<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create(
            'detail_setor_sampahs',

            function (
                Blueprint $table
            ) {

                $table->id();


                $table->foreignId(
                    'setor_sampah_id'
                )->constrained(
                    'setor_sampahs'
                )->onDelete(
                    'cascade'
                );


                $table->foreignId(
                    'jenis_sampah_id'
                )->constrained(
                    'jenis_sampahs'
                );


                $table->decimal(
                    'berat',
                    8,
                    2
                )->nullable();


                $table->integer(
                    'harga_per_kg'
                )->nullable();


                $table->integer(
                    'subtotal'
                )->nullable();


                $table->timestamps();
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'detail_setor_sampahs'
        );
    }
};