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
        // 🔹 USERS TABLE
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            // 🔹 ROLE (lebih fleksibel)
            $table->string('role')->default('nasabah');

            // 🔹 STATUS (langsung aktif biar bisa login)
            $table->string('status')->default('pending');

            // 🔹 DATA TAMBAHAN
            $table->string('no_hp')->nullable()->unique();
            $table->text('alamat')->nullable();

            // 🔹 RELASI KE BANK SAMPAH
            $table->foreignId('bank_sampah_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->rememberToken();
            $table->timestamps();
        });

        // 🔹 PASSWORD RESET
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ❌ SESSIONS DIHAPUS (karena kita pakai API / Flutter)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};