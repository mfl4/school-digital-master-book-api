<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel alumni
 * 
 * Menyimpan data alumni setelah lulus dari sekolah.
 * Alumni dapat login dan memperbarui data pribadi mereka.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alumni', function (Blueprint $table) {
            // Primary Key - NIM sebagai identifier utama
            $table->string('nim', 20)->primary()
                ->comment('Nomor Induk Mahasiswa/Alumni - Primary Key');

            // Data Dasar
            $table->string('name', 100)
                ->comment('Nama lengkap alumni');
            $table->year('graduation_year')
                ->comment('Tahun kelulusan');

            // Data Pendidikan Lanjutan (Opsional)
            $table->string('university', 100)->nullable()
                ->comment('Nama universitas/perguruan tinggi');

            // Data Pekerjaan (Opsional)
            $table->string('job_title', 100)->nullable()
                ->comment('Jabatan/posisi pekerjaan');
            $table->date('job_start')->nullable()
                ->comment('Tanggal mulai bekerja');
            $table->date('job_end')->nullable()
                ->comment('Tanggal selesai bekerja (null jika masih aktif)');

            // Kontak
            $table->string('phone', 20)->nullable()
                ->comment('Nomor telepon/WhatsApp');
            $table->string('email', 100)->nullable()
                ->comment('Email pribadi');

            // Sosial Media (Opsional)
            $table->string('linkedin', 255)->nullable()
                ->comment('URL profil LinkedIn');
            $table->string('instagram', 100)->nullable()
                ->comment('Username Instagram');
            $table->string('facebook', 255)->nullable()
                ->comment('URL atau username Facebook');
            $table->string('website', 255)->nullable()
                ->comment('Website pribadi atau portfolio');

            // Relasi ke siswa (jika dulunya siswa di sekolah ini)
            $table->string('nis', 20)->nullable()
                ->comment('NIS saat masih menjadi siswa');
            $table->foreign('nis')
                ->references('nis')->on('students')
                ->nullOnDelete();

            // Tracking perubahan data
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')->nullOnDelete()
                ->comment('User yang terakhir mengupdate');
            $table->ipAddress('updated_ip')->nullable()
                ->comment('IP address saat terakhir update');
            $table->timestamp('updated_at')->nullable()
                ->comment('Waktu terakhir update');

            $table->timestamp('created_at')->nullable();

            // Indexes untuk performa query
            $table->index('name');
            $table->index('graduation_year');
            $table->index('nis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
