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
        DB::statement("
            ALTER TABLE bank_sampahs
            MODIFY status
            ENUM(
                'pending',
                'active',
                'rejected'
            )
            DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE bank_sampahs
            MODIFY status
            ENUM(
                'pending',
                'aktif',
                'ditolak'
            )
            DEFAULT 'pending'
        ");
    }
};
