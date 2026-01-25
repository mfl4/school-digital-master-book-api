<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Grade Model (Nilai Raport)
 * 
 * Menyimpan nilai siswa per mata pelajaran per semester.
 * Observer: GradeObserver untuk auto-update grade_summary
 */
class Grade extends Model
{
    use HasFactory;

    /**
     * Grade letter constants
     */
    public const GRADE_LETTERS = [
        'A' => [90, 100],
        'B' => [80, 89],
        'C' => [70, 79],
        'D' => [60, 69],
        'E' => [0, 59],
    ];

    /**
     * Passing score constant
     */
    public const PASSING_SCORE = 75;

    /**
     * Atribut yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'student_id',
        'subject_id',
        'semester',
        'score',
        'last_edited_by',
        'last_edited_ip',
        'last_edited_at',
    ];

    /**
     * Casting atribut ke tipe data tertentu
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'last_edited_at' => 'datetime',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Relasi ke Student
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'nis');
    }

    /**
     * Relasi ke Subject
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Relasi ke User yang terakhir mengedit
     */
    public function lastEditor()
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Get grade letter (A/B/C/D/E) berdasarkan score
     */
    public function getGradeLetterAttribute(): string
    {
        foreach (self::GRADE_LETTERS as $letter => $range) {
            if ($this->score >= $range[0] && $this->score <= $range[1]) {
                return $letter;
            }
        }
        return 'E'; // Default jika tidak match
    }

    /**
     * Cek apakah nilai ini lulus (>= 75)
     */
    public function getIsPassingAttribute(): bool
    {
        return $this->score >= self::PASSING_SCORE;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope untuk filter berdasarkan semester
     */
    public function scopeBySemester($query, string $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope untuk filter berdasarkan student
     */
    public function scopeByStudent($query, string $nis)
    {
        return $query->where('student_id', $nis);
    }

    /**
     * Scope untuk filter berdasarkan subject
     */
    public function scopeBySubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope untuk filter berdasarkan student dan semester
     */
    public function scopeByStudentAndSemester($query, string $nis, string $semester)
    {
        return $query->where('student_id', $nis)
            ->where('semester', $semester);
    }

    /**
     * Scope untuk filter berdasarkan kelas (dari rombel_absen siswa)
     * Contoh: X-1, XI-2, XII-3
     */
    public function scopeByClass($query, string $class)
    {
        return $query->whereHas('student', function ($q) use ($class) {
            $q->where('rombel_absen', 'LIKE', $class . '-%');
        });
    }
}
