<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Daftar semester yang tersedia
        $semesters = [
            'Ganjil 2023/2024',
            'Genap 2023/2024',
            'Ganjil 2024/2025',
            'Genap 2024/2025',
        ];

        return [
            'student_id' => Student::inRandomOrder()->first()?->nis ?? 'default',
            'subject_id' => Subject::inRandomOrder()->first()?->id ?? 1,
            'semester' => fake()->randomElement($semesters),
            'score' => fake()->numberBetween(70, 100), // Realistic scores untuk testing
            'last_edited_by' => User::where('role', 'admin')->first()?->id ?? 1,
            'last_edited_ip' => fake()->ipv4(),
            'last_edited_at' => now(),
        ];
    }

    /**
     * Indicate that the grade has a perfect score.
     */
    public function perfect(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => 100,
        ]);
    }

    /**
     * Indicate that the grade is passing.
     */
    public function passing(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(75, 100),
        ]);
    }

    /**
     * Indicate that the grade is failing.
     */
    public function failing(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(0, 74),
        ]);
    }
}
