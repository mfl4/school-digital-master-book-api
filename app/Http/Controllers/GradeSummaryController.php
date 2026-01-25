<?php

namespace App\Http\Controllers;

use App\Models\GradeSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GradeSummaryController
 * 
 * Controller untuk menampilkan ringkasan nilai (Grade Summary).
 * Read-only controller (tidak ada create/update/delete karena auto-update via Observer).
 * 
 * Role-based access:
 * - Admin: Bisa lihat semua summaries
 * - Wali Kelas: Bisa lihat summaries siswa di kelasnya
 */
class GradeSummaryController extends Controller
{
    // =========================================================================
    // ADMIN METHODS
    // =========================================================================

    /**
     * Display a listing of grade summaries (Admin Only)
     * 
     * GET /api/grade-summaries
     * 
     * Query Parameters:
     * - student_id: Filter by student NIS
     * - semester: Filter by semester
     * - passing: Filter hanya yang lulus (true/false)
     * - per_page: Jumlah data per halaman (default: 15)
     */
    public function index(Request $request): JsonResponse
    {
        $query = GradeSummary::with(['student']);

        // Filter by student
        if ($request->filled('student_id')) {
            $query->byStudent($request->student_id);
        }

        // Filter by semester
        if ($request->filled('semester')) {
            $query->bySemester($request->semester);
        }

        // Filter by passing status
        if ($request->filled('passing') && $request->boolean('passing')) {
            $query->passing();
        }

        $summaries = $query->latest('calculated_at')->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Data grade summaries berhasil diambil',
            'data' => $summaries,
        ]);
    }

    /**
     * Display detail summary untuk student tertentu di semester tertentu (Admin Only)
     * 
     * GET /api/grade-summaries/{student_id}/{semester}
     */
    public function show(string $studentId, string $semester): JsonResponse
    {
        $summary = GradeSummary::with(['student', 'grades.subject'])
            ->where('student_id', '=', $studentId)
            ->where('semester', '=', $semester)
            ->firstOrFail();

        // Build response dengan detail grades per mapel
        $gradesDetail = $summary->grades->map(function ($grade) {
            return [
                'subject' => $grade->subject->name,
                'score' => $grade->score,
                'grade_letter' => $grade->grade_letter,
                'is_passing' => $grade->is_passing,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'nis' => $summary->student->nis,
                    'name' => $summary->student->name,
                    'class' => $summary->student->class,
                ],
                'semester' => $summary->semester,
                'total_score' => $summary->total_score,
                'average_score' => $summary->average_score,
                'grade_point_average' => $summary->grade_point_average,
                'status' => $summary->status,
                'calculated_at' => $summary->calculated_at,
                'grades' => $gradesDetail,
            ],
        ]);
    }

    // =========================================================================
    // WALI KELAS METHODS
    // =========================================================================

    /**
     * Display summaries untuk siswa di kelas wali kelas (Wali Kelas Only)
     * 
     * GET /api/wali/grade-summaries
     */
    public function classSummaries(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get summaries untuk siswa di kelas wali kelas
        $query = GradeSummary::with(['student'])
            ->byClass($user->class);

        // Filter by semester
        if ($request->filled('semester')) {
            $query->bySemester($request->semester);
        }

        // Filter by passing status
        if ($request->filled('passing') && $request->boolean('passing')) {
            $query->passing();
        }

        $summaries = $query->latest('calculated_at')->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Data grade summaries berhasil diambil',
            'data' => $summaries,
        ]);
    }
}
