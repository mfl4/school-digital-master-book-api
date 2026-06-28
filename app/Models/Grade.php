<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model Grade (Nilai Raport) - nilai siswa per mapel per semester
class Grade extends Model
{
    use HasFactory;

    // Konstanta grade letter (A, B, C, D, E)
    public const GRADE_LETTERS = [
        'A' => [90, 100],
        'B' => [80, 89],
        'C' => [70, 79],
        'D' => [60, 69],
        'E' => [0, 59],
    ];

    // Konstanta passing score (nilai lulus >= 75)
    public const PASSING_SCORE = 75;

    // Field yang boleh diisi secara mass assignment
    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_year_id',
        'semester',
        'score',
        'last_edited_by',
        'last_edited_ip',
        'last_edited_at',
    ];

    // Casting atribut ke tipe data tertentu
    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'last_edited_at' => 'datetime',
        ];
    }

    // === RELATIONSHIPS ===

    // Relasi ke Student
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'nis');
    }

    // Relasi ke Subject
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // Relasi ke User yang terakhir mengedit
    public function lastEditor()
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    // Relasi ke AcademicYear
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // === ACCESSORS ===

    // Get grade letter (A/B/C/D/E) berdasarkan score
    public function getGradeLetterAttribute(): string
    {
        foreach (self::GRADE_LETTERS as $letter => $range) {
            if ($this->score >= $range[0] && $this->score <= $range[1]) {
                return $letter;
            }
        }
        return 'E'; // Default jika tidak match
    }

    // Cek apakah nilai ini lulus (>= 75)
    public function getIsPassingAttribute(): bool
    {
        return $this->score >= self::PASSING_SCORE;
    }

    // === SCOPES ===

    // Filter berdasarkan semester
    public function scopeBySemester($query, string $semester)
    {
        return $query->where('semester', $semester);
    }

    // Filter berdasarkan tahun ajaran
    public function scopeByAcademicYear($query, int $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    // Filter berdasarkan student
    public function scopeByStudent($query, string $nis)
    {
        return $query->where('student_id', $nis);
    }

    // Filter berdasarkan subject
    public function scopeBySubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    // Filter berdasarkan student, academic_year, dan semester
    public function scopeByStudentTerm($query, string $nis, int $academicYearId, string $semester)
    {
        return $query->where('student_id', $nis)
            ->where('academic_year_id', $academicYearId)
            ->where('semester', $semester);
    }

    // Filter berdasarkan kelas (dari history classroom siswa di tahun ajaran tersebut)
    public function scopeByClass($query, $classId)
    {
        return $query->whereHas('student.classHistories', function ($q) use ($classId) {
            $q->where('classrooms.id', $classId)
              ->whereColumn('student_classrooms.academic_year_id', 'grades.academic_year_id');
        });
    }
}
