<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pengiriman_mitras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengiriman_id')
                ->constrained('pengiriman_mitras')
                ->onDelete('cascade');

            $table->string('jenis_sampah');

            $table->decimal('berat', 10, 2);

            $table->decimal('harga', 15, 2);

            $table->decimal('subtotal', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pengiriman_mitras');
    }
};