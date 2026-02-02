<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder
 * 
 * Membuat data user untuk testing:
 * 1. Admin users
 * 2. Guru users (linked to subjects)
 * 3. Wali Kelas users (linked to classes)
 * 4. Alumni users (linked to alumni data)
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $totalUsers = 0;

        // ====================
        // 1. ADMIN USERS
        // ====================
        
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'subject' => null,
            'class' => null,
            'alumni' => null,
        ]);
        $totalUsers++;

        $this->command->info("✓ Created 1 admin user");

        // ====================
        // 2. GURU USERS
        // ====================
        
        // Note: Subject IDs will be auto-incremented, so we use predictable IDs
        $guruData = [
            ['name' => 'Budi Santoso', 'email' => 'guru.matematika@mail.com', 'subject' => 1], // Matematika
            ['name' => 'Siti Rahayu', 'email' => 'guru.bahasa.indonesia@mail.com', 'subject' => 2], // Bahasa Indonesia
            ['name' => 'Ahmad Fauzi', 'email' => 'guru.bahasa.inggris@mail.com', 'subject' => 3], // Bahasa Inggris
            ['name' => 'Dewi Kusuma', 'email' => 'guru.fisika@mail.com', 'subject' => 4], // Fisika
            ['name' => 'Eko Wijaya', 'email' => 'guru.kimia@mail.com', 'subject' => 5], // Kimia
        ];

        foreach ($guruData as $guru) {
            User::create([
                'name' => $guru['name'],
                'email' => $guru['email'],
                'password' => Hash::make('password'),
                'role' => 'guru',
                'subject' => $guru['subject'],
                'class' => null,
                'alumni' => null,
            ]);
            $totalUsers++;
        }

        $this->command->info("✓ Created " . count($guruData) . " guru users");

        // ====================
        // 3. WALI KELAS USERS
        // ====================
        
        $waliKelasData = [
            ['name' => 'Siti Aminah', 'email' => 'wali.x1@mail.com', 'class' => 'X-1'],
            ['name' => 'Bambang Sutrisno', 'email' => 'wali.x2@mail.com', 'class' => 'X-2'],
            ['name' => 'Rina Setiawati', 'email' => 'wali.xi1@mail.com', 'class' => 'XI-1'],
            ['name' => 'Hendra Gunawan', 'email' => 'wali.xii1@mail.com', 'class' => 'XII-1'],
        ];

        foreach ($waliKelasData as $wali) {
            User::create([
                'name' => $wali['name'],
                'email' => $wali['email'],
                'password' => Hash::make('password'),
                'role' => 'wali_kelas',
                'subject' => null,
                'class' => $wali['class'],
                'alumni' => null,
            ]);
            $totalUsers++;
        }

        $this->command->info("✓ Created " . count($waliKelasData) . " wali kelas users");

        // ====================
        // 4. ALUMNI USERS (10 users linked to alumni)
        // ====================
        
        // Alumni users terhubung dengan data alumni yang sudah dibuat
        $alumniUsers = [
            ['name' => 'Andi Wijaya', 'email' => 'andi.wijaya@email.com', 'nim' => 'A2023001'],
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@email.com', 'nim' => 'A2023002'],
            ['name' => 'Citra Dewi', 'email' => 'citra.dewi@email.com', 'nim' => 'A2023003'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.lestari@email.com', 'nim' => 'A2023004'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko.prasetyo@email.com', 'nim' => 'A2023005'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri.handayani@email.com', 'nim' => 'A2022001'],
            ['name' => 'Galih Pratama', 'email' => 'galih.pratama@email.com', 'nim' => 'A2022002'],
            ['name' => 'Hana Permata', 'email' => 'hana.permata@email.com', 'nim' => 'A2022003'],
            ['name' => 'Indra Gunawan', 'email' => 'indra.gunawan@email.com', 'nim' => 'A2022004'],
            ['name' => 'Joko Susanto', 'email' => 'joko.susanto@email.com', 'nim' => 'A2022005'],
        ];

        foreach ($alumniUsers as $alum) {
            User::create([
                'name' => $alum['name'],
                'email' => $alum['email'],
                'password' => Hash::make('password'),
                'role' => 'alumni',
                'subject' => null,
                'class' => null,
                'alumni' => $alum['nim'], // Link to alumni table by NIM
            ]);
            $totalUsers++;
        }

        $this->command->info("✓ Created " . count($alumniUsers) . " alumni users");
        $this->command->info("✓ TOTAL: {$totalUsers} users created");
        $this->command->info("ℹ Default password for all users: 'password'");
    }
}
