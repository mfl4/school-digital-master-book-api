<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * StudentSeeder
 * 
 * Membuat data siswa contoh untuk setiap kelas.
 * Total: 12 kelas x 10 siswa = 120 siswa
 */
class StudentSeeder extends Seeder
{
    /**
     * Daftar kelas yang akan diisi
     */
    protected array $classes = [
        'X-1',
        'X-2',
        'X-3',
        'X-4',
        'XI-1',
        'XI-2',
        'XI-3',
        'XI-4',
        'XII-1',
        'XII-2',
        'XII-3',
        'XII-4',
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

        foreach ($this->classes as $class) {
            // Buat 10 siswa per kelas
            Student::factory()
                ->count(10)
                ->inClass($class)
                ->create([
                    'last_edited_by' => $admin?->id,
                    'last_edited_ip' => '127.0.0.1',
                    'last_edited_at' => now(),
                ]);

            $totalStudents += 10;
        }

        $this->command->info("Berhasil membuat {$totalStudents} siswa di " . count($this->classes) . " kelas.");
    }
}
