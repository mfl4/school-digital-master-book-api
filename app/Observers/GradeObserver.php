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
        $this->updateSummary($grade->student_id, $grade->semester);
    }

    /**
     * Handle the Grade "updated" event.
 * Dipanggil setelah grade di-update
     */
    public function updated(Grade $grade): void
    {
        $this->updateSummary($grade->student_id, $grade->semester);
    }

    /**
     * Handle the Grade "deleted" event.
     * Dipanggil setelah grade di-delete
     */
    public function deleted(Grade $grade): void
    {
        $this->updateSummary($grade->student_id, $grade->semester);
    }

    /**
     * Update atau create grade summary untuk student dan semester tertentu
     * 
     * @param string $studentId NIS siswa
     * @param string $semester Semester
     */
    private function updateSummary(string $studentId, string $semester): void
    {
        // Calculate total dan average dari grades
        $stats = Grade::where('student_id', '=', $studentId)
            ->where('semester', '=', $semester)
            ->selectRaw('COALESCE(SUM(score), 0) as total, COALESCE(AVG(score), 0) as average')
            ->first();

        // Update or Create summary
        GradeSummary::updateOrCreate(
            [
                'student_id' => $studentId,
                'semester' => $semester,
            ],
            [
                'total_score' => $stats->total ?? 0,
                'average_score' => round($stats->average ?? 0, 2),
                'calculated_at' => now(),
            ]
        );
    }
}
