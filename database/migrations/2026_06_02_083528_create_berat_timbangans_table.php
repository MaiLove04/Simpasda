<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berat_timbangans', function (Blueprint $table) {
            $table->id();
            $table->decimal('berat', 8, 2); // Menyimpan berat (contoh: 12.50 Kg)
            $table->string('device_id')->nullable(); // Opsional: ID alat IoT nya
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berat_timbangans');
    }
};