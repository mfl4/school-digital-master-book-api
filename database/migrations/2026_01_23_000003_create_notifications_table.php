<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel notifications
 * 
 * Menyimpan notifikasi untuk admin ketika ada perubahan data
 * (misalnya: alumni mengupdate data pribadinya)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->string('type', 50)
                ->comment('Tipe notifikasi (alumni_update, etc)');
            $table->text('message')
                ->comment('Pesan notifikasi');

            // Tracking siapa yang memicu notifikasi
            $table->foreignId('triggered_by')->nullable()
                ->constrained('users')->nullOnDelete()
                ->comment('User yang memicu notifikasi');
            $table->ipAddress('triggered_ip')->nullable()
                ->comment('IP address saat trigger');

            $table->boolean('is_read')->default(false)
                ->comment('Status sudah dibaca atau belum');

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('type');
            $table->index('is_read');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
