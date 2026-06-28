<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel grade_summaries (Ringkasan Nilai per Semester)
 * 
 * Menyimpan ringkasan nilai siswa per semester (total dan rata-rata).
 * Tabel ini akan di-update otomatis via Observer ketika ada perubahan di tabel grades.
 * 
 * Fitur:
 * - Composite unique: student_id + academic_year_id + semester (satu summary per siswa per tahun ajaran & semester)
 * - Auto-calculate via Observer: total_score, average_score
 * - Timestamp calculated_at untuk tracking kapan terakhir di-calculate
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grade_summaries', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->string('student_id', 20)
                ->comment('NIS siswa - foreign key ke students.nis');
            
            // Data Summary
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->cascadeOnDelete()
                ->comment('Tahun Ajaran');
            $table->enum('semester', ['odd', 'even'])
                ->comment('Semester ganjil/genap');
            $table->integer('total_score')->default(0)
                ->comment('Total nilai semua mata pelajaran');
            $table->decimal('average_score', 5, 2)->default(0.00)
                ->comment('Rata-rata nilai (2 digit desimal)');
            
            // Tambahan dari merged migration
            $table->string('class_name', 20)->nullable()
                ->comment('Kelas siswa saat semester ini berlangsung');
            $table->smallInteger('highest_score')->default(0)
                ->comment('Nilai tertinggi pada semester ini');
            $table->string('highest_subject', 100)->nullable()
                ->comment('Nama mapel dengan nilai tertinggi');
            $table->smallInteger('lowest_score')->default(0)
                ->comment('Nilai terendah pada semester ini');
            $table->string('lowest_subject', 100)->nullable()
                ->comment('Nama mapel dengan nilai terendah');
            
            // Tracking kalkulasi
            $table->timestamp('calculated_at')
                ->comment('Waktu terakhir kalkulasi');
            
            $table->timestamps();
            
            // Foreign Key Constraint
            $table->foreign('student_id')
                ->references('nis')->on('students')
                ->cascadeOnDelete();
            
            // Unique Constraint: Satu siswa hanya punya 1 summary per tahun ajaran & semester
            $table->unique(['student_id', 'academic_year_id', 'semester'], 'grade_summaries_unique_constraint');
            
            // Indexes untuk performa query
            $table->index('student_id');
            $table->index('academic_year_id');
            $table->index('semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_summaries');
    }
};
