<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_sampahs', function (Blueprint $table) {
            $table->renameColumn('nama', 'nama_bank');
        });
    }

    public function down(): void
    {
        Schema::table('bank_sampahs', function (Blueprint $table) {
            $table->renameColumn('nama_bank', 'nama');
        });
    }
};
