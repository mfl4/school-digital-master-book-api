<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * UserFactory
 * 
 * Factory untuk membuat data User dummy saat testing.
 * Menggunakan konstanta role dari User::ROLES.
 * 
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Password default yang di-cache untuk performa
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     * Default: role admin tanpa context fields
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'admin',
            'subject' => null,
            'class' => null,
            'alumni' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Set user sebagai admin
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'admin',
            'subject' => null,
            'class' => null,
            'alumni' => null,
        ]);
    }

    /**
     * Set user sebagai guru dengan subject_id
     */
    public function guru(?int $subjectId = null): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'guru',
            'subject' => $subjectId,
            'class' => null,
            'alumni' => null,
        ]);
    }

    /**
     * Set user sebagai wali kelas dengan class
     */
    public function waliKelas(?string $class = null): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'wali_kelas',
            'subject' => null,
            'class' => $class ?? 'X-1',
            'alumni' => null,
        ]);
    }

    /**
     * Set user sebagai alumni dengan nim
     */
    public function alumni(?string $nim = null): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'alumni',
            'subject' => null,
            'class' => null,
            'alumni' => $nim ?? fake()->numerify('A#######'),
        ]);
    }
}
