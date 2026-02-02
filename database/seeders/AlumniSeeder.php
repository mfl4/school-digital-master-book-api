<?php

namespace Database\Seeders;

use App\Models\Alumni;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * AlumniSeeder
 * 
 * Membuat data alumni:
 * 1. 10 alumni yang terhubung dengan graduated students (dengan NIS)
 * 2. 50 alumni lainnya (untuk data dummy tambahan)
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

        // ====================
        // 1. ALUMNI CONNECTED KE GRADUATED STUDENTS
        // ====================

        $graduatedStudentsData = [
            ['nis' => '2020001', 'nim' => 'A2023001', 'name' => 'Andi Wijaya', 'year' => 2023, 'university' => 'Universitas Indonesia', 'job' => 'Software Engineer'],
            ['nis' => '2020002', 'nim' => 'A2023002', 'name' => 'Budi Santoso', 'year' => 2023, 'university' => 'Institut Teknologi Bandung', 'job' => 'Data Scientist'],
            ['nis' => '2020003', 'nim' => 'A2023003', 'name' => 'Citra Dewi', 'year' => 2023, 'university' => 'Universitas Gadjah Mada', 'job' => 'Marketing Manager'],
            ['nis' => '2020004', 'nim' => 'A2023004', 'name' => 'Dewi Lestari', 'year' => 2023, 'university' => 'Universitas Brawijaya', 'job' => 'Teacher'],
            ['nis' => '2020005', 'nim' => 'A2023005', 'name' => 'Eko Prasetyo', 'year' => 2023, 'university' => 'Institut Pertanian Bogor', 'job' => 'Agricultural Consultant'],
            
            ['nis' => '2019001', 'nim' => 'A2022001', 'name' => 'Fitri Handayani', 'year' => 2022, 'university' => 'Universitas Airlangga', 'job' => 'Pharmacist'],
            ['nis' => '2019002', 'nim' => 'A2022002', 'name' => 'Galih Pratama', 'year' => 2022, 'university' => 'Universitas Diponegoro', 'job' => 'Civil Engineer'],
            ['nis' => '2019003', 'nim' => 'A2022003', 'name' => 'Hana Permata', 'year' => 2022, 'university' => 'Universitas Padjadjaran', 'job' => 'Journalist'],
            ['nis' => '2019004', 'nim' => 'A2022004', 'name' => 'Indra Gunawan', 'year' => 2022, 'university' => 'Universitas Hasanuddin', 'job' => 'Business Analyst'],
            ['nis' => '2019005', 'nim' => 'A2022005', 'name' => 'Joko Susanto', 'year' => 2022, 'university' => 'Universitas Sebelas Maret', 'job' => 'Graphic Designer'],
        ];

        foreach ($graduatedStudentsData as $alumData) {
            // Verify student exists
            $student = Student::where('nis', $alumData['nis'])->first();
            
            if (!$student) {
                $this->command->warn("Warning: Student with NIS {$alumData['nis']} not found. Skipping alumni creation.");
                continue;
            }

            Alumni::create([
                'nim' => $alumData['nim'],
                'name' => $alumData['name'],
                'graduation_year' => $alumData['year'],
                'university' => $alumData['university'],
                'job_title' => $alumData['job'],
                'job_start' => $alumData['year'] + 1 . '-01-01',
                'job_end' => null, // Still working
                'phone' => '08' . rand(1000000000, 9999999999),
                'email' => strtolower(str_replace(' ', '.', $alumData['name'])) . '@email.com',
                'linkedin' => 'https://linkedin.com/in/' . strtolower(str_replace(' ', '-', $alumData['name'])),
                'instagram' => '@' . strtolower(str_replace(' ', '_', $alumData['name'])),
                'facebook' => null,
                'website' => null,
                'nis' => $alumData['nis'], // Link to student
                'updated_by' => $admin?->id,
                'updated_ip' => '127.0.0.1',
                'updated_at' => now(),
                'created_at' => now()->subMonths(rand(1, 12)),
            ]);

            $totalAlumni++;
        }

        $this->command->info("✓ Created {$totalAlumni} alumni linked to graduated students");

        // ====================
        // 2. ALUMNI TAMBAHAN (Random, tidak linked ke students)
        // ====================

        // Buat alumni tambahan untuk tahun 2019-2021 (sebelum graduated students)
        for ($year = 2019; $year <= 2021; $year++) {
            // Buat 10 alumni per tahun
            $count = Alumni::factory()
                ->count(10)
                ->graduatedIn($year)
                ->create([
                    'updated_by' => $admin?->id,
                    'updated_ip' => '127.0.0.1',
                    'updated_at' => now(),
                ])
                ->count();

            $totalAlumni += $count;
        }

        $this->command->info("✓ Created 30 additional alumni (2019-2021)");

        // Buat beberapa alumni tambahan untuk tahun 2023-2024
        for ($year = 2023; $year <= 2024; $year++) {
            $count = Alumni::factory()
                ->count(5)
                ->graduatedIn($year)
                ->create([
                    'updated_by' => $admin?->id,
                    'updated_ip' => '127.0.0.1',
                    'updated_at' => now(),
                ])
                ->count();

            $totalAlumni += $count;
        }

        $this->command->info("✓ Created 10 additional alumni (2023-2024)");
        $this->command->info("✓ TOTAL: {$totalAlumni} alumni created");
    }
}
