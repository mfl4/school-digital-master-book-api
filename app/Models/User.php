<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Model User dengan berbagai role: admin, guru, wali_kelas, alumni
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLES = [
        'admin',
        'guru',
        'wali_kelas',
        'alumni',
    ];

    // Field yang boleh diisi secara mass assignment
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'subject',
        'class',
        'alumni',
    ];

    // Field yang disembunyikan saat serialisasi (password, token)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Casting atribut ke tipe data tertentu
    protected function casts(): array
    {
        return [];
    }

    // === HELPER METHODS ===

    // Cek apakah user memiliki role tertentu
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // Cek apakah user adalah Admin
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    // Cek apakah user adalah Guru
    public function isGuru(): bool
    {
        return $this->hasRole('guru');
    }

    // Cek apakah user adalah Wali Kelas
    public function isWaliKelas(): bool
    {
        return $this->hasRole('wali_kelas');
    }

    // Cek apakah user adalah Alumni
    public function isAlumni(): bool
    {
        return $this->hasRole('alumni');
    }

    // === RELATIONSHIPS ===

    // Relasi ke Subject (untuk role guru) - guru mengajar satu mata pelajaran
    public function subjectRelation()
    {
        return $this->belongsTo(Subject::class, 'subject');
    }

    // Relasi ke Alumni (untuk role alumni) - dihubungkan via NIM
    public function alumniRelation()
    {
        return $this->belongsTo(Alumni::class, 'alumni', 'nim');
    }

    // Relasi ke Students (untuk wali_kelas) - akses siswa di kelasnya 
    public function students()
    {
        return $this->hasMany(Student::class, 'rombel_absen', 'class')
            ->where('rombel_absen', 'LIKE', $this->class . '-%');
    }
}
