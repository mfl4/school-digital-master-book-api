<?php

namespace Database\Seeders;

use App\Models\Alumni;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * AlumniSeeder
 * 
 * Membuat data alumni contoh untuk beberapa tahun kelulusan.
 */
class AlumniSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset counter factory
        \Database\Factories\AlumniFactory::resetCounter();

        // Ambil admin untuk tracking
        $admin = User::where('role', 'admin')->first();

        $totalAlumni = 0;

        // Buat alumni untuk tahun 2020-2025
        for ($year = 2020; $year <= 2025; $year++) {
            // Buat 10 alumni per tahun
            Alumni::factory()
                ->count(10)
                ->graduatedIn($year)
                ->create([
                    'updated_by' => $admin?->id,
                    'updated_ip' => '127.0.0.1',
                    'updated_at' => now(),
                ]);

            $totalAlumni += 10;
        }

        // Update user alumni agar terhubung dengan data alumni
        // (untuk user alumni@mail.com yang ada di UserSeeder)
        $alumniUser = User::where('role', 'alumni')->first();
        if ($alumniUser && $alumniUser->alumni) {
            // Pastikan ada data alumni dengan NIM yang sesuai
            $existingAlumni = Alumni::find($alumniUser->alumni);
            if (!$existingAlumni) {
                // Buat alumni khusus untuk user alumni
                Alumni::create([
                    'nim' => $alumniUser->alumni,
                    'name' => $alumniUser->name,
                    'graduation_year' => 2020,
                    'university' => 'Universitas Indonesia',
                    'job_title' => 'Software Engineer',
                    'job_start' => '2022-01-01',
                    'phone' => '081234567890',
                    'email' => $alumniUser->email,
                    'updated_by' => $admin?->id,
                    'updated_ip' => '127.0.0.1',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
                $totalAlumni++;
            }
        }

        $this->command->info("Berhasil membuat {$totalAlumni} data alumni.");
    }
}
