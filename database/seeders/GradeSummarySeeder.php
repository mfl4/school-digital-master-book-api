<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\GradeSummary;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * GradeSummarySeeder
 * 
 * Seeder untuk recalculate dan verify grade summaries.
 * 
 * NOTE: Grade summaries seharusnya auto-created oleh GradeObserver,
 * tapi seeder ini memastikan semua summaries up-to-date.
 */
class GradeSummarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("Calculating grade summaries...");

        // Get all unique student-semester combinations from grades
        $combinations = Grade::select('student_id', 'semester')
            ->distinct()
            ->get();

        $totalSummaries = 0;
        $updated = 0;
        $created = 0;

        foreach ($combinations as $combo) {
            // Calculate total and average for this student-semester
            $summary = Grade::where('student_id', $combo->student_id)
                ->where('semester', $combo->semester)
                ->selectRaw('
                    SUM(score) as total_score,
                    AVG(score) as average_score,
                    COUNT(*) as subject_count
                ')
                ->first();

            if (!$summary || !$summary->subject_count) {
                continue;
            }

            // Update or create grade summary
            $gradeSummary = GradeSummary::updateOrCreate(
                [
                    'student_id' => $combo->student_id,
                    'semester' => $combo->semester,
                ],
                [
                    'total_score' => round($summary->total_score, 2),
                    'average_score' => round($summary->average_score, 2),
                    'calculated_at' => now(),
                ]
            );

            if ($gradeSummary->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }

            $totalSummaries++;
        }

        $this->command->info("✓ Grade summaries processed:");
        $this->command->info("  - Created: {$created}");
        $this->command->info("  - Updated: {$updated}");
        $this->command->info("  - Total: {$totalSummaries}");

        // Verify integrity
        $gradesWithoutSummary = Grade::select('student_id', 'semester')
            ->distinct()
            ->get()
            ->filter(function ($grade) {
                return !GradeSummary::where('student_id', $grade->student_id)
                    ->where('semester', $grade->semester)
                    ->exists();
            })
            ->count();

        if ($gradesWithoutSummary > 0) {
            $this->command->warn("⚠ Warning: {$gradesWithoutSummary} student-semester combinations have grades but no summary!");
        } else {
            $this->command->info("✓ All grades have corresponding summaries");
        }

        // Show sample summary
        $sampleSummary = GradeSummary::with('student')
            ->orderBy('calculated_at', 'desc')
            ->first();

        if ($sampleSummary) {
            $this->command->info("\nSample Summary:");
            $this->command->info("  Student: " . ($sampleSummary->student->name ?? 'N/A'));
            $this->command->info("  Semester: {$sampleSummary->semester}");
            $this->command->info("  Total Score: {$sampleSummary->total_score}");
            $this->command->info("  Average Score: {$sampleSummary->average_score}");
        }
    }
}
