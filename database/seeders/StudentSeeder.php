<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * StudentSeeder
 * 
 * Membuat data siswa contoh:
 * 1. Siswa aktif untuk kelas X, XI, XII (12 kelas x 10 siswa = 120)
 * 2. Siswa lulus (10 siswa) yang akan connected ke alumni
 */
class StudentSeeder extends Seeder
{
    /**
     * Daftar kelas aktif
     */
    protected array $activeClasses = [
        1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset counter factory
        \Database\Factories\StudentFactory::resetCounter();

        // Ambil admin untuk tracking
        $admin = User::where('role', 'admin')->first();

        $totalStudents = 0;

        // ====================
        // 1. SISWA AKTIF
        // ====================
        foreach ($this->activeClasses as $classId) {
            // Buat 10 siswa per kelas
            Student::factory()
                ->count(10)
                ->inClass($classId)
                ->create([
                    'last_edited_by' => $admin?->id,
                    'last_edited_ip' => '127.0.0.1',
                    'last_edited_at' => now(),
                ]);

            $totalStudents += 10;
        }

        $this->command->info("✓ Created {$totalStudents} active students in " . count($this->activeClasses) . " classes");

        // ====================
        // 2. SISWA LULUS (untuk linked ke alumni)
        // ====================
        
        // Data siswa yang sudah lulus (akan terhubung dengan alumni)
        $graduatedStudents = [
            ['nis' => '2020001', 'name' => 'Andi Wijaya', 'year' => 2023],
            ['nis' => '2020002', 'name' => 'Budi Santoso', 'year' => 2023],
            ['nis' => '2020003', 'name' => 'Citra Dewi', 'year' => 2023],
            ['nis' => '2020004', 'name' => 'Dewi Lestari', 'year' => 2023],
            ['nis' => '2020005', 'name' => 'Eko Prasetyo', 'year' => 2023],
            
            ['nis' => '2019001', 'name' => 'Fitri Handayani', 'year' => 2022],
            ['nis' => '2019002', 'name' => 'Galih Pratama', 'year' => 2022],
            ['nis' => '2019003', 'name' => 'Hana Permata', 'year' => 2022],
            ['nis' => '2019004', 'name' => 'Indra Gunawan', 'year' => 2022],
            ['nis' => '2019005', 'name' => 'Joko Susanto', 'year' => 2022],
        ];

        foreach ($graduatedStudents as $grad) {
            Student::create([
                'nis' => $grad['nis'],
                'nisn' => '0' . rand(100000000, 999999999),
                'name' => $grad['name'],
                'gender' => rand(0, 1) ? 'L' : 'P', // L=Laki-laki, P=Perempuan
                'birth_place' => 'Jakarta',
                'birth_date' => '2005-' . rand(1, 12) . '-' . rand(1, 28),
                'religion' => 'Islam',
                'father_name' => 'Ayah ' . $grad['name'],
                'address' => 'Jl. Contoh No. ' . rand(1, 100) . ', Jakarta Selatan',
                'ijazah_number' => 'IJ-' . $grad['year'] . '-' . rand(1000, 9999),
                'last_edited_by' => $admin?->id,
                'last_edited_ip' => '127.0.0.1',
                'last_edited_at' => now(),
                'created_at' => now()->subYears(3),
                'updated_at' => now()->subYears(3),
            ]);

            $totalStudents++;
        }

        $this->command->info("✓ Created " . count($graduatedStudents) . " graduated students (for alumni linking)");
        $this->command->info("✓ TOTAL: {$totalStudents} students created");
    }
}
