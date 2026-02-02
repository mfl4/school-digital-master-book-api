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
        'rombel_absen',
        'last_edited_by',
        'last_edited_ip',
        'last_edited_at',
    ];

    // Atribut yang selalu disertakan dalam JSON (class, gender_label)
    protected $appends = ['class', 'gender_label'];

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

    // === ACCESSORS & MUTATORS ===

    // Get label jenis kelamin (L -> Laki-laki, P -> Perempuan)
    public function getGenderLabelAttribute(): string
    {
        return self::GENDERS[$this->gender] ?? $this->gender;
    }

    // Get kelas dari rombel_absen (X-1-01 -> X-1)
    public function getClassAttribute(): string
    {
        $parts = explode('-', $this->rombel_absen);
        return count($parts) >= 2 ? $parts[0] . '-' . $parts[1] : $this->rombel_absen;
    }

    // Get nomor absen dari rombel_absen (X-1-01 -> 01)
    public function getAbsenAttribute(): string
    {
        $parts = explode('-', $this->rombel_absen);
        return count($parts) >= 3 ? $parts[2] : '';
    }

    // === SCOPES ===

    // Filter berdasarkan kelas (X-1, XI-2, dll)
    public function scopeByClass($query, string $class)
    {
        return $query->where('rombel_absen', 'LIKE', $class . '-%');
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
