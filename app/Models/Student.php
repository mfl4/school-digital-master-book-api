<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model Student (Data Induk Siswa) - NIS sebagai primary key
class Student extends Model
{
    use HasFactory;

    // Primary key adalah NIS (string), bukan auto-increment
    protected $primaryKey = 'nis';
    public $incrementing = false;
    protected $keyType = 'string';

    // Konstanta jenis kelamin
    public const GENDERS = [
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
    ];

    // Konstanta agama yang valid
    public const RELIGIONS = [
        'Islam',
        'Kristen',
        'Katolik',
        'Hindu',
        'Buddha',
        'Konghucu',
    ];

    // Field yang boleh diisi secara mass assignment
    protected $fillable = [
        'nis',
        'nisn',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'father_name',
        'address',
        'ijazah_number',
        'last_edited_by',
        'last_edited_ip',
        'last_edited_at',
    ];

    // Atribut yang selalu disertakan dalam JSON (class, gender_label, status)
    protected $appends = ['class', 'gender_label', 'status'];
    
    protected $with = ['classHistories']; // Eager load class history

    // Casting atribut ke tipe data tertentu
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'last_edited_at' => 'datetime',
        ];
    }

    // === RELATIONSHIPS ===

    // Relasi ke User yang terakhir mengedit student ini
    public function lastEditor()
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    // Relasi ke Alumni (jika siswa ini sudah lulus)
    public function alumni()
    {
        return $this->hasOne(Alumni::class, 'nis', 'nis');
    }

    // Relasi ke Classroom History
    public function classHistories()
    {
        return $this->belongsToMany(Classroom::class, 'student_classrooms', 'student_id', 'classroom_id', 'nis')
                    ->withPivot('academic_year_id', 'id')
                    ->orderByPivot('id', 'desc')
                    ->withTimestamps();
    }

    // === ACCESSORS & MUTATORS ===

    // Get label jenis kelamin (L -> Laki-laki, P -> Perempuan)
    public function getGenderLabelAttribute(): string
    {
        return self::GENDERS[$this->gender] ?? $this->gender;
    }

    // Get kelas dari relasi classHistories (mengambil kelas terbaru/terakhir yang diassign)
    public function getClassAttribute(): ?string
    {
        $latestClass = $this->classHistories->first();
        return $latestClass ? $latestClass->name : null;
    }

    // Get status siswa (siswa / alumni)
    public function getStatusAttribute(): string
    {
        return $this->relationLoaded('alumni') ? ($this->alumni ? 'alumni' : 'siswa') : 'siswa';
    }

    // === SCOPES ===

    // Filter berdasarkan kelas (ID kelas) - mencari history kelas
    public function scopeByClass($query, $classId)
    {
        return $query->whereHas('classHistories', function ($q) use ($classId) {
            $q->where('classrooms.id', $classId);
        });
    }

    // Filter berdasarkan jenis kelamin
    public function scopeByGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    // Pencarian berdasarkan NIS, NISN, atau nama
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nis', 'ILIKE', "%{$search}%")
                ->orWhere('nisn', 'ILIKE', "%{$search}%")
                ->orWhere('name', 'ILIKE', "%{$search}%");
        });
    }
}
