<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarik_tunais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->bigInteger('jumlah_nominal');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('tanggal_request')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarik_tunais');
    }
};
