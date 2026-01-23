<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * AlumniFactory
 * 
 * Factory untuk membuat data Alumni dummy saat testing.
 * 
 * @extends Factory<Alumni>
 */
class AlumniFactory extends Factory
{
    /**
     * Model yang terhubung dengan factory ini
     */
    protected $model = Alumni::class;

    /**
     * Counter untuk NIM unik
     */
    protected static int $nimCounter = 1;

    /**
     * Daftar universitas populer di Indonesia
     */
    protected static array $universities = [
        'Universitas Indonesia',
        'Institut Teknologi Bandung',
        'Universitas Gadjah Mada',
        'Institut Pertanian Bogor',
        'Universitas Airlangga',
        'Universitas Padjadjaran',
        'Universitas Diponegoro',
        'Universitas Brawijaya',
        'Universitas Hasanuddin',
        'Institut Teknologi Sepuluh Nopember',
        'Universitas Negeri Jakarta',
        'Universitas Pendidikan Indonesia',
        'Universitas Islam Negeri',
        'Politeknik Negeri Jakarta',
        'Universitas Bina Nusantara',
    ];

    /**
     * Daftar pekerjaan umum
     */
    protected static array $jobTitles = [
        'Software Engineer',
        'Data Analyst',
        'Guru',
        'Dokter',
        'Akuntan',
        'Marketing Manager',
        'Human Resource',
        'Desainer Grafis',
        'Content Creator',
        'Entrepreneur',
        'Pegawai Negeri Sipil',
        'Bank Teller',
        'Konsultan',
        'Journalist',
        'Researcher',
    ];

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // Generate NIM unik (format: A + tahun + nomor urut)
        $graduationYear = fake()->numberBetween(2018, 2025);
        $nim = 'A' . $graduationYear . str_pad(self::$nimCounter++, 3, '0', STR_PAD_LEFT);

        // Generate nama Indonesia
        $gender = fake()->randomElement(['male', 'female']);
        $name = $gender === 'male'
            ? fake('id_ID')->firstNameMale() . ' ' . fake('id_ID')->lastName()
            : fake('id_ID')->firstNameFemale() . ' ' . fake('id_ID')->lastName();

        // Apakah sudah bekerja?
        $hasJob = fake()->boolean(60);
        $jobStart = $hasJob ? fake()->dateTimeBetween('-5 years', '-1 month') : null;

        return [
            'nim' => $nim,
            'name' => $name,
            'graduation_year' => $graduationYear,
            'university' => fake()->optional(0.7)->randomElement(self::$universities),
            'job_title' => $hasJob ? fake()->randomElement(self::$jobTitles) : null,
            'job_start' => $jobStart?->format('Y-m-d'),
            'job_end' => ($hasJob && fake()->boolean(20))
                ? fake()->dateTimeBetween($jobStart, 'now')->format('Y-m-d')
                : null,
            'phone' => fake('id_ID')->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'linkedin' => fake()->optional(0.4)->url(),
            'instagram' => fake()->optional(0.5)->userName(),
            'facebook' => fake()->optional(0.3)->url(),
            'website' => fake()->optional(0.2)->url(),
            'nis' => null,
            'updated_by' => null,
            'updated_ip' => null,
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    /**
     * Set tahun kelulusan tertentu
     */
    public function graduatedIn(int $year): static
    {
        return $this->state(function (array $attributes) use ($year) {
            $nim = 'A' . $year . str_pad(self::$nimCounter++, 3, '0', STR_PAD_LEFT);
            return [
                'nim' => $nim,
                'graduation_year' => $year,
            ];
        });
    }

    /**
     * Set sebagai alumni yang melanjutkan kuliah
     */
    public function inUniversity(?string $university = null): static
    {
        return $this->state(fn(array $attributes) => [
            'university' => $university ?? fake()->randomElement(self::$universities),
        ]);
    }

    /**
     * Set sebagai alumni yang sudah bekerja
     */
    public function employed(?string $jobTitle = null): static
    {
        $jobStart = fake()->dateTimeBetween('-5 years', '-1 month');

        return $this->state(fn(array $attributes) => [
            'job_title' => $jobTitle ?? fake()->randomElement(self::$jobTitles),
            'job_start' => $jobStart->format('Y-m-d'),
            'job_end' => null,
        ]);
    }

    /**
     * Link ke student
     */
    public function forStudent(Student $student): static
    {
        return $this->state(fn(array $attributes) => [
            'nis' => $student->nis,
            'name' => $student->name,
        ]);
    }

    /**
     * Set updated info
     */
    public function updatedBy(User $user, ?string $ip = null): static
    {
        return $this->state(fn(array $attributes) => [
            'updated_by' => $user->id,
            'updated_ip' => $ip ?? fake()->ipv4(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reset counter untuk testing
     */
    public static function resetCounter(): void
    {
        self::$nimCounter = 1;
    }
}
