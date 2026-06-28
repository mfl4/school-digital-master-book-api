<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel grades (Nilai Raport)
 * 
 * Menyimpan nilai raport siswa per mata pelajaran per semester.
 * 
 * Fitur:
 * - Composite unique: student_id + subject_id + academic_year_id + semester (tidak boleh duplikasi)
 * - CHECK constraint: score harus 0-100
 * - Auto tracking: last_edited_by, last_edited_ip, last_edited_at
 * - Cascade delete jika student atau subject dihapus
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->string('student_id', 20)
                ->comment('NIS siswa - foreign key ke students.nis');
            $table->unsignedBigInteger('subject_id')
                ->comment('ID mata pelajaran');
            
            // Data Nilai
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->cascadeOnDelete()
                ->comment('Tahun Ajaran');
            $table->enum('semester', ['odd', 'even'])
                ->comment('Semester ganjil/genap');
            $table->smallInteger('score')
                ->comment('Nilai siswa (0-100)');
            
            // Tracking perubahan data
            $table->foreignId('last_edited_by')->nullable()
                ->constrained('users')->nullOnDelete()
                ->comment('User yang terakhir mengedit');
            $table->ipAddress('last_edited_ip')->nullable()
                ->comment('IP address saat terakhir edit');
            $table->timestamp('last_edited_at')->nullable()
                ->comment('Waktu terakhir edit');
            
            $table->timestamps();
            
            // Foreign Key Constraints
            $table->foreign('student_id')
                ->references('nis')->on('students')
                ->cascadeOnDelete();
            
            $table->foreign('subject_id')
                ->references('id')->on('subjects')
                ->cascadeOnDelete();
            
            // Unique Constraint: Satu siswa hanya boleh punya 1 nilai per mapel per tahun ajaran & semester
            $table->unique(['student_id', 'subject_id', 'academic_year_id', 'semester'], 'grades_unique_constraint');
            
            // Indexes untuk performa query
            $table->index('student_id');
            $table->index('subject_id');
            $table->index('academic_year_id');
            $table->index('semester');
            $table->index(['student_id', 'academic_year_id', 'semester'], 'idx_student_academic_semester');
        });
        
        // CHECK Constraint untuk validasi score (0-100)
        // PostgreSQL syntax
        DB::statement('ALTER TABLE grades ADD CONSTRAINT check_score_range CHECK (score >= 0 AND score <= 100)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
