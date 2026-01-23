<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Alumni Model
 * 
 * Menyimpan data alumni setelah lulus dari sekolah.
 * NIM digunakan sebagai primary key.
 */
class Alumni extends Model
{
    use HasFactory;

    /**
     * Nama tabel (singular untuk alumni)
     */
    protected $table = 'alumni';

    /**
     * Primary key adalah string (NIM), bukan auto-increment
     */
    protected $primaryKey = 'nim';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Disable default timestamps karena menggunakan custom updated_at
     */
    public $timestamps = false;

    /**
     * Atribut yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'nim',
        'name',
        'graduation_year',
        'university',
        'job_title',
        'job_start',
        'job_end',
        'phone',
        'email',
        'linkedin',
        'instagram',
        'facebook',
        'website',
        'nis',
        'updated_by',
        'updated_ip',
        'updated_at',
        'created_at',
    ];

    /**
     * Casting atribut ke tipe data tertentu
     */
    protected function casts(): array
    {
        return [
            'graduation_year' => 'integer',
            'job_start' => 'date',
            'job_end' => 'date',
            'updated_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Relasi ke Student (data saat masih menjadi siswa)
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'nis', 'nis');
    }

    /**
     * Relasi ke User yang terakhir mengupdate
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relasi ke User account (jika alumni memiliki akun)
     */
    public function userAccount()
    {
        return $this->hasOne(User::class, 'alumni', 'nim');
    }

    // =========================================================================
    // ACCESSORS & MUTATORS
    // =========================================================================

    /**
     * Cek apakah alumni masih bekerja
     */
    public function getIsCurrentlyWorkingAttribute(): bool
    {
        return $this->job_title && $this->job_start && !$this->job_end;
    }

    /**
     * Get tahun sejak lulus
     */
    public function getYearsSinceGraduationAttribute(): int
    {
        return now()->year - $this->graduation_year;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope untuk filter berdasarkan tahun kelulusan
     */
    public function scopeByGraduationYear($query, int $year)
    {
        return $query->where('graduation_year', $year);
    }

    /**
     * Scope untuk filter alumni yang sudah bekerja
     */
    public function scopeEmployed($query)
    {
        return $query->whereNotNull('job_title');
    }

    /**
     * Scope untuk filter alumni yang sudah kuliah
     */
    public function scopeInUniversity($query)
    {
        return $query->whereNotNull('university');
    }

    /**
     * Scope untuk pencarian berdasarkan nama atau NIM
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nim', 'ILIKE', "%{$search}%")
                ->orWhere('name', 'ILIKE', "%{$search}%")
                ->orWhere('email', 'ILIKE', "%{$search}%");
        });
    }
}
