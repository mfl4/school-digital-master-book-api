<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * NotificationSeeder
 * 
 * Membuat sample notifikasi untuk testing dashboard admin.
 * Notifications dibuat triggered oleh berbagai user actions.
 */
class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalNotifications = 0;

        // Get sample users
        $alumniUsers = User::where('role', 'alumni')->take(5)->get();
        $admin = User::where('role', 'admin')->first();

        // ====================
        // SAMPLE NOTIFICATIONS
        // ====================

        $sampleNotifications = [
            // Alumni updates
            [
                'type' => 'alumni_update',
                'message' => 'Alumni Andi Wijaya telah memperbarui data profil (Universitas, Pekerjaan)',
                'triggered_by' => $alumniUsers->get(0)?->id ?? null,
                'triggered_ip' => '192.168.1.10',
                'is_read' => false,
                'created_at' => now()->subMinutes(5),
            ],
            [
                'type' => 'alumni_update',
                'message' => 'Alumni Budi Santoso telah memperbarui data profil (Email, No. Telepon)',
                'triggered_by' => $alumniUsers->get(1)?->id ?? null,
                'triggered_ip' => '192.168.1.11',
                'is_read' => false,
                'created_at' => now()->subMinutes(30),
            ],
            [
                'type' => 'alumni_update',
                'message' => 'Alumni Citra Dewi telah memperbarui data profil (Pekerjaan, Social Media)',
                'triggered_by' => $alumniUsers->get(2)?->id ?? null,
                'triggered_ip' => '192.168.1.12',
                'is_read' => true,
                'created_at' => now()->subHours(2),
            ],
            [
                'type' => 'alumni_update',
                'message' => 'Alumni Dewi Lestari telah memperbarui data profil (Universitas)',
                'triggered_by' => $alumniUsers->get(3)?->id ?? null,
                'triggered_ip' => '192.168.1.13',
                'is_read' => true,
                'created_at' => now()->subHours(5),
            ],
            [
                'type' => 'alumni_update',
                'message' => 'Alumni Eko Prasetyo telah memperbarui data profil (Pekerjaan, Tahun Kerja)',
                'triggered_by' => $alumniUsers->get(4)?->id ?? null,
                'triggered_ip' => '192.168.1.14',
                'is_read' => true,
                'created_at' => now()->subDay(),
            ],

            // System notifications
            [
                'type' => 'system',
                'message' => 'Backup database berhasil dilakukan',
                'triggered_by' => $admin?->id,
                'triggered_ip' => '127.0.0.1',
                'is_read' => false,
                'created_at' => now()->subHours(1),
            ],
            [
                'type' => 'system',
                'message' => 'Maintenance terjadwal pada 5 Februari 2026 pukul 01:00 WIB',
                'triggered_by' => $admin?->id,
                'triggered_ip' => '127.0.0.1',
                'is_read' => false,
                'created_at' => now()->subHours(3),
            ],
            [
                'type' => 'system',
                'message' => 'Update sistem berhasil dilakukan ke versi 1.2.0',
                'triggered_by' => $admin?->id,
                'triggered_ip' => '127.0.0.1',
                'is_read' => true,
                'created_at' => now()->subDays(2),
            ],

            // Data changes
            [
                'type' => 'data_change',
                'message' => 'Nilai raport semester Ganjil 2024/2025 untuk kelas XII-1 telah diinput',
                'triggered_by' => $admin?->id,
                'triggered_ip' => '192.168.1.5',
                'is_read' => true,
                'created_at' => now()->subDays(3),
            ],
            [
                'type' => 'data_change',
                'message' => 'Data siswa baru kelas X-1 telah ditambahkan (5 siswa)',
                'triggered_by' => $admin?->id,
                'triggered_ip' => '192.168.1.5',
                'is_read' => true,
                'created_at' => now()->subDays(5),
            ],
        ];

        foreach ($sampleNotifications as $notification) {
            if ($notification['triggered_by']) {
                Notification::create($notification);
                $totalNotifications++;
            }
        }

        $this->command->info("✓ Created {$totalNotifications} sample notifications");
        $this->command->info("ℹ Unread: " . Notification::where('is_read', false)->count());
        $this->command->info("ℹ Read: " . Notification::where('is_read', true)->count());
    }
}
