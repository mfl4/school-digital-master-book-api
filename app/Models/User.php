<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 * 
 * Role yang tersedia:
 * - admin: Akses penuh ke semua fitur
 * - guru: Guru mata pelajaran, hanya bisa input nilai mapel yang diampu
 * - wali_kelas: Wali kelas, bisa akses semua data siswa di kelasnya
 * - alumni: Alumni, bisa update data pribadi
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLES = [
        'admin',
        'guru',
        'wali_kelas',
        'alumni',
    ];

    /**
     * Atribut yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'subject',
        'class',
        'alumni',
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut ke tipe data tertentu
     */
    protected function casts(): array
    {
        return [];
    }

    // =========================================================================
    // HELPER METHODS - Untuk pengecekan role
    // =========================================================================

    /**
     * Cek apakah user memiliki role tertentu
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Cek apakah user adalah Admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Cek apakah user adalah Guru
     */
    public function isGuru(): bool
    {
        return $this->hasRole('guru');
    }

    /**
     * Cek apakah user adalah Wali Kelas
     */
    public function isWaliKelas(): bool
    {
        return $this->hasRole('wali_kelas');
    }

    /**
     * Cek apakah user adalah Alumni
     */
    public function isAlumni(): bool
    {
        return $this->hasRole('alumni');
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Relasi ke Subject (untuk role guru)
     * Teacher hanya bisa mengajar SATU mata pelajaran
     */
    // public function subjectRelation()
    // {
    //     return $this->belongsTo(Subject::class, 'subject');
    // }

    /**
 * Relasi ke Alumni (untuk role alumni)
 * Alumni dihubungkan via nim
 */
    // public function alumniRelation()
    // {
    //     return $this->belongsTo(Alumni::class, 'alumni', 'nim');
    // }
}
