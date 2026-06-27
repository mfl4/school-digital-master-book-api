<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model GradeSummary (Ringkasan Nilai per Semester) - auto-update via GradeObserver
class GradeSummary extends Model
{
    // Field yang boleh diisi secara mass assignment
    protected $fillable = [
        'student_id',
        'semester',
        'class_name',
        'total_score',
        'average_score',
        'highest_score',
        'highest_subject',
        'lowest_score',
        'lowest_subject',
        'calculated_at',
    ];

    // Casting atribut ke tipe data tertentu
    protected function casts(): array
    {
        return [
            'average_score' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    // === RELATIONSHIPS ===

    // Relasi ke Student
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'nis');
    }

    // Relasi ke Grades (untuk mendapatkan detail nilai per mapel)
    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id', 'student_id')
            ->where('grades.semester', '=', $this->semester);
    }

    // === ACCESSORS ===

    // Ambil GPA (Indeks Prestasi) format 0.00 - 4.00
    public function getGradePointAverageAttribute(): float
    {
        // Konversi dari rata-rata 0-100 ke GPA 0-4
        return round(($this->average_score / 100) * 4, 2);
    }

    // Get status kelulusan berdasarkan rata-rata (Lulus jika >= 75)
    public function getStatusAttribute(): string
    {
        return $this->average_score >= 75 ? 'Lulus' : 'Tidak Lulus';
    }

    // === SCOPES ===

    // Filter berdasarkan semester
    public function scopeBySemester($query, string $semester)
    {
        return $query->where('semester', $semester);
    }

    // Filter berdasarkan student
    public function scopeByStudent($query, string $nis)
    {
        return $query->where('student_id', $nis);
    }

    // Filter berdasarkan status kelulusan (average >= 75)
    public function scopePassing($query)
    {
        return $query->where('average_score', '>=', 75);
    }

    // Filter berdasarkan kelas (dari student)
    public function scopeByClass($query, string $class)
    {
        return $query->whereHas('student', function ($q) use ($class) {
            $q->where('rombel_absen', 'LIKE', $class . '-%');
        });
    }

    // === HELPER METHODS ===

    // Hitung ulang ringkasan dari nilai (dipanggil oleh Observer)
    public function recalculate(): void
    {
        // Calculate basic stats
        $stats = Grade::where('student_id', $this->student_id)
            ->where('semester', $this->semester)
            ->selectRaw('COALESCE(SUM(score), 0) as total, COALESCE(AVG(score), 0) as average')
            ->first();

        // Get student's current class name
        $student = Student::find($this->student_id);
        $className = $student && $student->rombel_absen ? explode('-', $student->rombel_absen)[0] . '-' . explode('-', $student->rombel_absen)[1] : null;

        // Find highest score and subject
        $highestGrade = Grade::with('subject')
            ->where('student_id', $this->student_id)
            ->where('semester', $this->semester)
            ->orderBy('score', 'desc')
            ->first();

        // Find lowest score and subject
        $lowestGrade = Grade::with('subject')
            ->where('student_id', $this->student_id)
            ->where('semester', $this->semester)
            ->orderBy('score', 'asc')
            ->first();

        $this->update([
            'class_name' => $className,
            'total_score' => $stats->total ?? 0,
            'average_score' => $stats->average ?? 0,
            'highest_score' => $highestGrade ? $highestGrade->score : 0,
            'highest_subject' => $highestGrade && $highestGrade->subject ? $highestGrade->subject->name : null,
            'lowest_score' => $lowestGrade ? $lowestGrade->score : 0,
            'lowest_subject' => $lowestGrade && $lowestGrade->subject ? $lowestGrade->subject->name : null,
            'calculated_at' => now(),
        ]);
    }

    // Cek apakah lulus (average >= 75)
    public function isPassing(): bool
    {
        return $this->average_score >= 75;
    }
}
