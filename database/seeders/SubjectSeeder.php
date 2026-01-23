<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * SubjectSeeder
 * 
 * Membuat data mata pelajaran contoh untuk sekolah.
 * Semua mata pelajaran dibuat oleh admin.
 */
class SubjectSeeder extends Seeder
{
    /**
     * Daftar mata pelajaran yang akan dibuat
     */
    protected array $subjects = [
        'Matematika',
        'Bahasa Indonesia',
        'Bahasa Inggris',
        'Fisika',
        'Kimia',
        'Biologi',
        'Sejarah',
        'Geografi',
        'Ekonomi',
        'Sosiologi',
        'Pendidikan Agama Islam',
        'Pendidikan Kewarganegaraan',
        'Seni Budaya',
        'Pendidikan Jasmani',
        'Prakarya dan Kewirausahaan',
        'Informatika',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user admin pertama sebagai creator
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->command->warn('Admin user tidak ditemukan. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        foreach ($this->subjects as $subjectName) {
            Subject::firstOrCreate(
                ['name' => $subjectName],
                ['created_by' => $admin->id]
            );
        }

        $this->command->info('Berhasil membuat ' . count($this->subjects) . ' mata pelajaran.');
    }
}
