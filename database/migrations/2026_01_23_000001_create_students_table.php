<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel students (Data Induk Siswa)
 * 
 * Berdasarkan format Model 8355 dari MAN 4 Jakarta Selatan.
 * Field yang ada:
 * - NIS: Nomor Induk Siswa (Primary Key)
 * - NISN: Nomor Induk Siswa Nasional
 * - Data pribadi siswa
 * - Data orang tua
 * - Tracking perubahan data
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            // Primary Key - NIS sebagai identifier utama
            $table->string('nis', 20)->primary()
                ->comment('Nomor Induk Siswa - Primary Key');

            // Identitas Siswa
            $table->string('nisn', 20)->unique()
                ->comment('Nomor Induk Siswa Nasional');
            $table->string('name', 100)
                ->comment('Nama lengkap siswa');
            $table->enum('gender', ['L', 'P'])
                ->comment('Jenis kelamin: L=Laki-laki, P=Perempuan');
            $table->string('birth_place', 50)
                ->comment('Tempat lahir');
            $table->date('birth_date')
                ->comment('Tanggal lahir');
            $table->enum('religion', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])
                ->default('Islam')
                ->comment('Agama siswa');

            // Data Orang Tua
            $table->string('father_name', 100)
                ->comment('Nama ayah');

            // Alamat
            $table->text('address')
                ->comment('Alamat lengkap termasuk RT/RW');

            // Data Akademik
            $table->string('ijazah_number', 50)->nullable()
                ->comment('Nomor seri ijazah SMP/MTs');
            $table->string('rombel_absen', 10)
                ->comment('Rombongan belajar dan nomor absen (misal: X-1-01)');

            // Tracking perubahan data
            $table->foreignId('last_edited_by')->nullable()
                ->constrained('users')->nullOnDelete()
                ->comment('User yang terakhir mengedit');
            $table->ipAddress('last_edited_ip')->nullable()
                ->comment('IP address saat terakhir edit');
            $table->timestamp('last_edited_at')->nullable()
                ->comment('Waktu terakhir edit');

            $table->timestamps();

            // Indexes untuk performa query
            $table->index('nisn');
            $table->index('name');
            $table->index('rombel_absen');
            $table->index('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
