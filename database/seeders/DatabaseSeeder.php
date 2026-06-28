<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder
 * 
 * Seeder utama yang memanggil seeder lainnya.
 * Urutan eksekusi sangat penting karena ada dependency antar tabel.
 * 
 * URUTAN SEEDING:
 * 1. Users       - Base users (admin, guru, wali kelas, alumni)
 * 2. Subjects    - Mata pelajaran (needed for guru users & grades)
 * 3. Students    - Siswa aktif + siswa lulus
 * 4. Alumni      - Alumni data (linked to graduated students)
 * 5. Grades      - Nilai siswa per subject & semester
 * 6. GradeSummary - Auto-calculate grade summaries
 * 7. Notifications - Sample notifications untuk testing
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info("╔══════════════════════════════════════════════╗");
        $this->command->info("║   SEEDING DATABASE - BUKU INDUK DIGITAL      ║");
        $this->command->info("╚══════════════════════════════════════════════╝");
        $this->command->newLine();

        $startTime = microtime(true);

        $this->call([
            // 0. Classrooms - Must be first (foreign key for users and students)
            ClassroomSeeder::class,

            // 0.5. Academic Years - Must be first (foreign key for grades)
            AcademicYearSeeder::class,

            // 1. Users - Base users (admin, guru, wali kelas, alumni)
            UserSeeder::class,
            
            // 2. Subjects - Needed for guru users and grades
            SubjectSeeder::class,
            
            // 3. Students - Includes active students + graduated students
            StudentSeeder::class,
            
            // 4. Alumni - Linked to graduated students (NIS)
            AlumniSeeder::class,
            
            // 5. Grades - Student grades per subject & semester
            // Note: GradeObserver will auto-create grade_summaries
            GradeSeeder::class,
            
            // 6. Grade Summaries - Recalculate and verify
            GradeSummarySeeder::class,
            
            // 7. Notifications - Sample notifications for testing
            NotificationSeeder::class,
        ]);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->command->newLine();
        $this->command->info("╔══════════════════════════════════════════════╗");
        $this->command->info("║          SEEDING COMPLETED SUCCESSFULLY       ║");
        $this->command->info("╚══════════════════════════════════════════════╝");
        $this->command->info("⏱ Duration: {$duration} seconds");
        $this->command->newLine();

        // Display summary
        $this->displaySummary();
    }

    /**
     * Display seeding summary
     */
    protected function displaySummary(): void
    {
        $this->command->info("📊 SEEDING SUMMARY:");
        $this->command->newLine();

        $tables = [
            ['Table', 'Count', 'Description'],
            ['─────────────────', '─────', '──────────────────────────'],
            ['users', \App\Models\User::count(), 'System users'],
            ['subjects', \App\Models\Subject::count(), 'Mata pelajaran'],
            ['students', \App\Models\Student::count(), 'Siswa (aktif + lulus)'],
            ['alumni', \App\Models\Alumni::count(), 'Alumni'],
            ['grades', \App\Models\Grade::count(), 'Nilai siswa'],
            ['grade_summaries', \App\Models\GradeSummary::count(), 'Ringkasan nilai'],
            ['notifications', \App\Models\Notification::count(), 'Notifikasi'],
        ];

        foreach ($tables as $row) {
            $this->command->info(sprintf('%-20s %-7s %s', $row[0], $row[1], $row[2]));
        }

        $this->command->newLine();
        $this->command->info("🔐 TEST CREDENTIALS:");
        $this->command->info("  Admin:         admin@school.sch.id / password");
        $this->command->info("  Guru:          guru.matematika@school.sch.id / password");
        $this->command->info("  Wali Kelas:    wali.x1@school.sch.id / password");
        $this->command->info("  Alumni:        andi.wijaya@school.sch.id / password");
        $this->command->newLine();

        $this->command->info("💡 NEXT STEPS:");
        $this->command->info("  1. Test login dengan credentials di atas");
        $this->command->info("  2. Verify dashboard menampilkan data dengan benar");
        $this->command->info("  3. Test CRUD operations pada setiap halaman");
        $this->command->info("  4. Verify grade summaries calculated correctly");
        $this->command->newLine();
    }
}
