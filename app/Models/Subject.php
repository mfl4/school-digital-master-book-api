<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Subject Model (Mata Pelajaran)
 * 
 * Menyimpan data mata pelajaran yang diajarkan di sekolah.
 * Setiap subject bisa diajarkan oleh beberapa guru.
 */
class Subject extends Model
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'name',
        'created_by',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Relasi ke User yang membuat subject ini
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User yang mengajar subject ini (role: guru)
     * Satu subject bisa diajarkan oleh beberapa guru
     */
    public function teachers()
    {
        return $this->hasMany(User::class, 'subject')
            ->where('role', 'guru');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope untuk pencarian berdasarkan nama subject
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'ILIKE', "%{$search}%");
    }
}
