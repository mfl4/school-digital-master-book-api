<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model Subject (Mata Pelajaran) - menyimpan data mapel yang diajarkan
class Subject extends Model
{
    use HasFactory;

    // Field yang boleh diisi secara mass assignment
    protected $fillable = [
        'name',
        'code',
        'created_by',
    ];

    // === RELATIONSHIPS ===

    // Relasi ke User yang membuat subject ini
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke User yang mengajar subject ini (role: guru)
    public function teachers()
    {
        return $this->hasMany(User::class, 'subject')
            ->where('role', 'guru');
    }

    // === SCOPES ===

    // Pencarian berdasarkan nama subject
    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'ILIKE', "%{$search}%");
    }
}
