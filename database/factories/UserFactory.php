<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::ADMIN,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Set user sebagai admin
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ADMIN,
        ]);
    }

    /**
     * Set user sebagai guru dengan subject_id
     */
    public function guru(?int $subjectId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::GURU,
            'subject' => $subjectId,
        ]);
    }

    /**
     * Set user sebagai wali kelas dengan class
     */
    public function waliKelas(?string $class = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::WALI_KELAS,
            'class' => $class ?? 'X-1',
        ]);
    }

    /**
     * Set user sebagai alumni dengan nim
     */
    public function alumni(?string $nim = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ALUMNI,
            'alumni' => $nim ?? fake()->numerify('##########'),
        ]);
    }
}
