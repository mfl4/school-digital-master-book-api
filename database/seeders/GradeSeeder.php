<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * GradeSeeder
 * 
 * Seeder untuk generate dummy data grades.
 * Membuat grades untuk setiap kombinasi student x subject x semester.
 * 
 * IMPORTANT: Tidak menggunakan WithoutModelEvents agar GradeObserver ter-trigger
 * dan auto-create grade_summaries.
 */
class GradeSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students, subjects, dan admin user
        $students = Student::all();
        $subjects = Subject::all();
        $adminUser = User::where('role', 'admin')->first();

        $academicYears = \App\Models\AcademicYear::all();
        // Daftar semester
        $semesters = [
            'odd',
            'even',
        ];

        // Counter untuk tracking progress
        $totalGrades = 0;

        // Generate grades untuk setiap kombinasi
        foreach ($students as $student) {
            foreach ($academicYears as $year) {
                foreach ($semesters as $semester) {
                    foreach ($subjects as $subject) {
                        Grade::create([
                            'student_id' => $student->nis,
                            'subject_id' => $subject->id,
                            'academic_year_id' => $year->id,
                            'semester' => $semester,
                            'score' => rand(70, 100), // Random score antara 70-100
                            'last_edited_by' => $adminUser?->id,
                            'last_edited_ip' => '127.0.0.1',
                            'last_edited_at' => now(),
                        ]);
                        
                        $totalGrades++;
                    }
                }
            }
        }

        $this->command->info("✓ Created {$totalGrades} grades");
        $this->command->info("✓ Grade summaries will be auto-created by GradeObserver");
    }
}
