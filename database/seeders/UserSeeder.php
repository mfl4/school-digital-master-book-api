<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder
 * 
 * Membuat data user contoh untuk testing.
 * Setiap user hanya memiliki SATU role.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin - Akses penuh ke semua fitur
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'subject' => null,
            'class' => null,
            'alumni' => null,
        ]);

        // Guru Matematika - Hanya bisa input nilai Matematika pada semua siswa di semua kelas
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'guru.matematika@mail.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'subject' => 1,
            'class' => null,
            'alumni' => null,
        ]);

        // Wali Kelas X-1 - Bisa akses semua data siswa kelas X-1 termasuk nilai rapornya
        User::create([
            'name' => 'Siti Aminah',
            'email' => 'wali.x1@mail.com',
            'password' => Hash::make('password'),
            'role' => 'wali_kelas',
            'subject' => null,
            'class' => 'X-1',
            'alumni' => null,
        ]);

        // Alumni - Bisa update data pribadi
        User::create([
            'name' => 'Ahmad Ridwan',
            'email' => 'alumni@mail.com',
            'password' => Hash::make('password'),
            'role' => 'alumni',
            'subject' => null,
            'class' => null,
            'alumni' => 'A2020001',
        ]);
    }
}
