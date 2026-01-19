<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat user admin default
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ]);

        // Buat user guru untuk testing
        User::factory()->guru()->create([
            'name' => 'Guru Matematika',
            'email' => 'guru@mail.com',
            'password' => Hash::make('password'),
        ]);

        // Buat user wali kelas untuk testing
        User::factory()->waliKelas('X-1')->create([
            'name' => 'Wali Kelas X-1',
            'email' => 'walikelas@mail.com',
            'password' => Hash::make('password'),
        ]);

        // Buat user alumni untuk testing
        User::factory()->alumni('2024001')->create([
            'name' => 'Alumni Test',
            'email' => 'alumni@mail.com',
            'password' => Hash::make('password'),
        ]);

        // Info untuk development
        $this->command->info('');
        $this->command->info('=== User Testing Berhasil Dibuat ===');
        $this->command->info('');
        $this->command->table(
            ['Email', 'Password', 'Role'],
            [
                ['admin@mail.com', 'password', 'admin'],
                ['guru@mail.com', 'password', 'guru'],
                ['walikelas@mail.com', 'password', 'wali_kelas'],
                ['alumni@mail.com', 'password', 'alumni'],
            ]
        );
    }
}
