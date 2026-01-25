<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder
 * 
 * Seeder utama yang memanggil seeder lainnya.
 * Urutan eksekusi penting karena ada dependency antar tabel.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Users harus pertama karena menjadi foreign key di tabel lain
            UserSeeder::class,

            // 2. Subjects membutuhkan user (created_by)
            SubjectSeeder::class,

            // 3. Students membutuhkan user (last_edited_by)
            StudentSeeder::class,

            // 4. Alumni membutuhkan students (nis) dan user (updated_by)
            AlumniSeeder::class,

            // 5. Grades membutuhkan students, subjects, dan users
            // Grade summaries akan auto-created oleh GradeObserver
            GradeSeeder::class,
        ]);
    }
}
