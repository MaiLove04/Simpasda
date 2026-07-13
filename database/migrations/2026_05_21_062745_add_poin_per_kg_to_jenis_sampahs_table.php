<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_sampahs', function (Blueprint $table) {

            $table->integer('poin_per_kg')
                  ->default(0)
                  ->after('harga_per_kg');

        });
    }

    public function down(): void
    {
        Schema::table('jenis_sampahs', function (Blueprint $table) {

            $table->dropColumn('poin_per_kg');

        });
    }
};