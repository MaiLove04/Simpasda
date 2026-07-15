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
    Schema::table('setor_sampahs', function (Blueprint $table) {
        // Ganti total_harga menjadi total sesuai nama kolom di database kamu
        $table->string('foto_sampah')->nullable()->after('total'); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('setor_sampahs', function (Blueprint $table) {
        $table->dropColumn('foto_sampah');
    });
}
};
