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
        // 1. Tambahkan kolom saldo ke tabel users (default 0 dan ditaruh setelah kolom email/password)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'saldo')) {
                $table->bigInteger('saldo')->default(0)->after('password');
            }
        });

        // 2. Buat tabel mutasi_saldos baru untuk track record Midtrans & Setoran
        Schema::create('mutasi_saldos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('jenis_transaksi', ['masuk', 'keluar']); // masuk = Kredit, keluar = Debit
            $table->enum('sumber', ['setor_sampah', 'tarik_tunai']); 
            $table->unsignedBigInteger('referensi_id')->nullable(); // ID dari transaksi setor_sampahs atau Midtrans
            $table->bigInteger('nominal');
            $table->enum('status', ['pending', 'success', 'failed'])->default('success'); // tarik_tunai bisa pending dulu
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_saldos');
        
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'saldo')) {
                $table->dropColumn('saldo');
            }
        });
    }
};