<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * SubjectSeeder
 * 
 * Membuat data mata pelajaran untuk sekolah.
 * Includes subject codes untuk better organization.
 */
class SubjectSeeder extends Seeder
{
    /**
     * Daftar mata pelajaran dengan kode
     */
    protected array $subjects = [
        ['name' => 'Matematika', 'code' => 'MTK'],
        ['name' => 'Bahasa Indonesia', 'code' => 'BIND'],
        ['name' => 'Bahasa Inggris', 'code' => 'BING'],
        ['name' => 'Fisika', 'code' => 'FIS'],
        ['name' => 'Kimia', 'code' => 'KIM'],
        ['name' => 'Biologi', 'code' => 'BIO'],
        ['name' => 'Sejarah', 'code' => 'SEJ'],
        ['name' => 'Geografi', 'code' => 'GEO'],
        ['name' => 'Ekonomi', 'code' => 'EKO'],
        ['name' => 'Sosiologi', 'code' => 'SOS'],
        ['name' => 'Pendidikan Agama Islam', 'code' => 'PAI'],
        ['name' => 'Pendidikan Kewarganegaraan', 'code' => 'PKN'],
        ['name' => 'Seni Budaya', 'code' => 'SBD'],
        ['name' => 'Pendidikan Jasmani', 'code' => 'PJOK'],
        ['name' => 'Prakarya dan Kewirausahaan', 'code' => 'PKWU'],
        ['name' => 'Informatika', 'code' => 'INF'],
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

        $created = 0;
        $existing = 0;

        foreach ($this->subjects as $subjectData) {
            $subject = Subject::firstOrCreate(
                ['name' => $subjectData['name']],
                [
                    'code' => $subjectData['code'],
                    'created_by' => $admin->id,
                ]
            );

            if ($subject->wasRecentlyCreated) {
                $created++;
            } else {
                // Update code if it doesn't exist
                if (!$subject->code) {
                    $subject->update(['code' => $subjectData['code']]);
                }
                $existing++;
            }
        }

        $this->command->info("✓ Subjects processed:");
        $this->command->info("  - Created: {$created}");
        $this->command->info("  - Existing: {$existing}");
        $this->command->info("  - Total: " . count($this->subjects));
    }
}
