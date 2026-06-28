<?php

namespace App\Observers;

use App\Models\Grade;
use App\Models\GradeSummary;

/**
 * GradeObserver
 * 
 * Observer untuk model Grade yang otomatis mengupdate grade_summary
 * ketika ada perubahan data grades (created, updated, deleted).
 */
class GradeObserver
{
    /**
     * Handle the Grade "created" event.
     * Dipanggil setelah grade baru di-insert
     */
    public function created(Grade $grade): void
    {
        $this->updateSummary($grade->student_id, $grade->academic_year_id, $grade->semester);
    }

    /**
     * Handle the Grade "updated" event.
 * Dipanggil setelah grade di-update
     */
    public function updated(Grade $grade): void
    {
        $this->updateSummary($grade->student_id, $grade->academic_year_id, $grade->semester);
    }

    /**
     * Handle the Grade "deleted" event.
     * Dipanggil setelah grade di-delete
     */
    public function deleted(Grade $grade): void
    {
        $this->updateSummary($grade->student_id, $grade->academic_year_id, $grade->semester);
    }

    /**
     * Update atau create grade summary untuk student dan semester tertentu
     * 
     * @param string $studentId NIS siswa
     * @param string $semester Semester
     */
    private function updateSummary(string $studentId, int $academicYearId, string $semester): void
    {
        // Get or Create summary
        $summary = GradeSummary::firstOrCreate(
            [
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
                'semester' => $semester,
            ]
        );

        // Call recalculate to update all stats including min/max and class
        $summary->recalculate();
    }
}
