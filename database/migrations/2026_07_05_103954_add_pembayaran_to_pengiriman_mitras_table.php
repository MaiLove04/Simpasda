<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengiriman_mitras', function (Blueprint $table) {

            $table->string('bukti_transfer')->nullable()->after('metode_pembayaran');

            $table->timestamp('tanggal_pembayaran')->nullable()->after('bukti_transfer');

            $table->text('catatan_verifikasi')->nullable()->after('keterangan');

        });
    }

    public function down(): void
    {
        Schema::table('pengiriman_mitras', function (Blueprint $table) {

            $table->dropColumn([
                'bukti_transfer',
                'tanggal_pembayaran',
                'catatan_verifikasi'
            ]);

        });
    }
};