<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * SubjectFactory
 * 
 * Factory untuk membuat data Subject (Mata Pelajaran) dummy saat testing.
 * 
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Model yang terhubung dengan factory ini
     */
    protected $model = Subject::class;

    /**
     * Daftar nama mata pelajaran untuk sekolah Indonesia
     */
    protected static array $subjects = [
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
     * Counter untuk memastikan nama unik
     */
    protected static int $subjectIndex = 0;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // Menggunakan nama mata pelajaran secara berurutan
        $name = self::$subjects[self::$subjectIndex % count(self::$subjects)];
        self::$subjectIndex++;

        return [
            'name' => $name,
            'created_by' => User::factory()->admin(),
        ];
    }

    /**
     * Set creator dari subject
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'created_by' => $user->id,
        ]);
    }

    /**
     * Set nama subject secara spesifik
     */
    public function withName(string $name): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * Reset counter untuk testing
     */
    public static function resetIndex(): void
    {
        self::$subjectIndex = 0;
    }
}
