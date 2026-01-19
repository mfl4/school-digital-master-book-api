<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel users
 *
 * Deskripsi:
 * - Setiap user HANYA boleh memiliki SATU role
 * - Field subject, class, alumni bersifat opsional tergantung role
 * - Menggunakan CHECK constraint untuk validasi role di level database
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            // Role: HANYA BOLEH SATU per user
            // Menggunakan ENUM untuk validasi di level database (PostgreSQL)
            $table->enum('role', ['admin', 'guru', 'wali_kelas', 'alumni'])
                ->default('alumni')
                ->comment('Role pengguna - setiap user hanya boleh punya 1 role');

            // Field opsional berdasarkan role
            $table->unsignedBigInteger('subject')->nullable()
                ->comment('ID mata pelajaran - hanya untuk role guru');
            $table->string('class', 10)->nullable()
                ->comment('Kode kelas (X-1, XI-2, XII-3) - hanya untuk role wali_kelas');
            $table->string('alumni', 20)->nullable()
                ->comment('NIM alumni - hanya untuk role alumni');

            $table->rememberToken();
            $table->timestamps();

            // Index untuk performa query
            $table->index('role');
            $table->index('subject');
            $table->index('class');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
