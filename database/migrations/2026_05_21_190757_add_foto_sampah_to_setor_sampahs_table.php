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
    // Fitur 'foto_sampah' telah dihapus.
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
