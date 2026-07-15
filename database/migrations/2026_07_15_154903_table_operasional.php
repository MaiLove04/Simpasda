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
        
        Schema::create('operasional', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_transaksi', ['Pemasukan', 'Pengeluaran']);
            $table->string('kategori');
            $table->float('harga', 15, 2);
            $table->float('jumlah', 15, 2);
            $table->float('total', 15, 2);
            $table->text('keterangan');
            $table->enum('sumber', ['Manual', 'Setor Sampah', 'Tarik Tunai', 'Pengiriman Mitra', 'Lainnya'])->default('Manual');
            $table->string('kode_referensi');
            $table->string('tanggal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
