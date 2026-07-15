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
        Schema::table('mutasi_saldos', function (Blueprint $table) {
            $table->string('status')->default('success')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_saldos', function (Blueprint $table) {
            $table->enum('status', ['pending', 'success', 'rejected'])->default('success')->change();
        });
    }
};
