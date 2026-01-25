<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * GradeSummary Model (Ringkasan Nilai per Semester)
 * 
 * Menyimpan ringkasan nilai siswa per semester.
 * Auto-update via GradeObserver ketika ada perubahan di tabel grades.
 */
class GradeSummary extends Model
{
    /**
     * Atribut yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'student_id',
        'semester',
        'total_score',
        'average_score',
        'calculated_at',
    ];

    /**
     * Casting atribut ke tipe data tertentu
     */
    protected function casts(): array
    {
        return [
            'average_score' => 'decimal:2',
            'calculated_at' => 'datetime',
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
     * Relasi ke Grades (untuk mendapatkan detail nilai per mapel)
     */
    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id', 'student_id')
            ->where('grades.semester', '=', $this->semester);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Get GPA (Grade Point Average) format 0.00 - 4.00
     * Konversi dari rata-rata 0-100 ke GPA 0-4
     */
    public function getGradePointAverageAttribute(): float
    {
        // Rumus konversi: GPA = (average_score / 100) * 4
        return round(($this->average_score / 100) * 4, 2);
    }

    /**
     * Get status kelulusan berdasarkan rata-rata
     * Lulus jika rata-rata >= 75
     */
    public function getStatusAttribute(): string
    {
        return $this->average_score >= 75 ? 'Lulus' : 'Tidak Lulus';
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
     * Scope untuk filter berdasarkan status kelulusan
     */
    public function scopePassing($query)
    {
        return $query->where('average_score', '>=', 75);
    }

    /**
     * Scope untuk filter berdasarkan kelas (dari student)
     */
    public function scopeByClass($query, string $class)
    {
        return $query->whereHas('student', function ($q) use ($class) {
            $q->where('rombel_absen', 'LIKE', $class . '-%');
        });
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Recalculate summary dari grades
     * Method ini dipanggil oleh Observer
     */
    public function recalculate(): void
    {
        $stats = Grade::where('student_id', $this->student_id)
            ->where('semester', $this->semester)
            ->selectRaw('COALESCE(SUM(score), 0) as total, COALESCE(AVG(score), 0) as average')
            ->first();

        $this->update([
            'total_score' => $stats->total ?? 0,
            'average_score' => $stats->average ?? 0,
            'calculated_at' => now(),
        ]);
    }

    /**
     * Cek apakah lulus (average >= 75)
     */
    public function isPassing(): bool
    {
        return $this->average_score >= 75;
    }
}
