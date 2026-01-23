<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * StudentFactory
 * 
 * Factory untuk membuat data Student (Siswa) dummy saat testing.
 * 
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Model yang terhubung dengan factory ini
     */
    protected $model = Student::class;

    /**
     * Counter untuk NIS unik
     */
    protected static int $nisCounter = 1;

    /**
     * Daftar kelas yang tersedia
     */
    protected static array $classes = [
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
     * Define the model's default state.
     */
    public function definition(): array
    {
        // Generate NIS unik
        $nis = '2024' . str_pad(self::$nisCounter++, 4, '0', STR_PAD_LEFT);

        // Generate NISN (10 digit)
        $nisn = '00' . fake()->unique()->numerify('########');

        // Pilih kelas random dan nomor absen
        $class = fake()->randomElement(self::$classes);
        $absen = str_pad(fake()->numberBetween(1, 36), 2, '0', STR_PAD_LEFT);

        // Gender untuk nama yang sesuai
        $gender = fake()->randomElement(['L', 'P']);
        $name = $gender === 'L'
            ? fake('id_ID')->firstNameMale() . ' ' . fake('id_ID')->lastName()
            : fake('id_ID')->firstNameFemale() . ' ' . fake('id_ID')->lastName();

        return [
            'nis' => $nis,
            'nisn' => $nisn,
            'name' => $name,
            'gender' => $gender,
            'birth_place' => fake('id_ID')->city(),
            'birth_date' => fake()->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
            'religion' => fake()->randomElement(Student::RELIGIONS),
            'father_name' => fake('id_ID')->firstNameMale() . ' ' . fake('id_ID')->lastName(),
            'address' => fake('id_ID')->address() . ' RT ' . fake()->numberBetween(1, 15) . '/RW ' . fake()->numberBetween(1, 10),
            'ijazah_number' => fake()->optional(0.7)->regexify('DN-[A-Z]{2}/[0-9]{6}/[0-9]{4}'),
            'rombel_absen' => $class . '-' . $absen,
            'last_edited_by' => null,
            'last_edited_ip' => null,
            'last_edited_at' => null,
        ];
    }

    /**
     * Set kelas tertentu
     */
    public function inClass(string $class): static
    {
        return $this->state(function (array $attributes) use ($class) {
            $absen = str_pad(fake()->numberBetween(1, 36), 2, '0', STR_PAD_LEFT);
            return [
                'rombel_absen' => $class . '-' . $absen,
            ];
        });
    }

    /**
     * Set jenis kelamin laki-laki
     */
    public function male(): static
    {
        return $this->state(fn(array $attributes) => [
            'gender' => 'L',
            'name' => fake('id_ID')->firstNameMale() . ' ' . fake('id_ID')->lastName(),
        ]);
    }

    /**
     * Set jenis kelamin perempuan
     */
    public function female(): static
    {
        return $this->state(fn(array $attributes) => [
            'gender' => 'P',
            'name' => fake('id_ID')->firstNameFemale() . ' ' . fake('id_ID')->lastName(),
        ]);
    }

    /**
     * Set agama Islam
     */
    public function muslim(): static
    {
        return $this->state(fn(array $attributes) => [
            'religion' => 'Islam',
        ]);
    }

    /**
     * Set dengan ijazah SMP/MTs
     */
    public function withIjazah(): static
    {
        return $this->state(fn(array $attributes) => [
            'ijazah_number' => fake()->regexify('DN-[A-Z]{2}/[0-9]{6}/[0-9]{4}'),
        ]);
    }

    /**
     * Set last edited info
     */
    public function editedBy(User $user, ?string $ip = null): static
    {
        return $this->state(fn(array $attributes) => [
            'last_edited_by' => $user->id,
            'last_edited_ip' => $ip ?? fake()->ipv4(),
            'last_edited_at' => now(),
        ]);
    }

    /**
     * Reset counter untuk testing
     */
    public static function resetCounter(): void
    {
        self::$nisCounter = 1;
    }
}
