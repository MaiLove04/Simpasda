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

            function (
                Blueprint $table
            ) {

                $table
                    ->foreignId(
                        'jenis_sampah_id'
                    )

                    ->nullable()

                    ->change();
            }
        );
    }



    public function down(): void
    {

        Schema::table(

            'setor_sampahs',

            function (
                Blueprint $table
            ) {

                $table
                    ->foreignId(
                        'jenis_sampah_id'
                    )

                    ->nullable(false)

                    ->change();
            }
        );
    }
};