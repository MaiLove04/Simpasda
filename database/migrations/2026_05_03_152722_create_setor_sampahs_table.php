<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setor_sampahs', function (Blueprint $table) {

            $table->uuid('id')->primary();


            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('kurir_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            $table->foreignId(
                'jenis_sampah_id'
            )->constrained(
                'jenis_sampahs'
            );


            $table->text('catatan')
                  ->nullable();


            $table->double('berat')
                  ->nullable();


            $table->integer('harga_per_kg')
                  ->nullable();


            $table->integer('total')
                  ->nullable();


            $table->string('status')
                  ->default(
                      'pending'
                  );


            $table->timestamps();
        });
    }
};