<?php

namespace App\Http\Controllers;

use App\Models\GradeSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Controller read-only untuk menampilkan ringkasan nilai (auto-update via Observer)
class GradeSummaryController extends Controller
{
    // === ADMIN METHODS ===

    // Mengambil semua grade summaries dengan filter (student, semester, passing)
    public function index(Request $request): JsonResponse
    {
        $query = GradeSummary::with(['student']);

        // Filter berdasarkan siswa
        }

        // Filter berdasarkan semester
        if ($request->filled('semester')) {
            $query->bySemester($request->semester);
        }

        // Filter berdasarkan status kelulusan
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

    // Menampilkan detail summary untuk student tertentu di semester tertentu
    public function show(string $studentId, string $semester): JsonResponse
    {
        $summary = GradeSummary::with(['student', 'grades.subject'])
            ->where('student_id', '=', $studentId)
            ->where('semester', '=', $semester)
            ->firstOrFail();

        // Buat response dengan detail nilai per mapel
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

    // === WALI KELAS METHODS ===

    // Mengambil summaries untuk siswa di kelas wali kelas
    public function classSummaries(Request $request): JsonResponse
    {
        $user = $request->user();

        // Ambil summary untuk siswa di kelas wali kelas
        $query = GradeSummary::with(['student'])
            ->byClass($user->class);

        // Filter berdasarkan semester
        if ($request->filled('semester')) {
            $query->bySemester($request->semester);
        }

        // Filter berdasarkan status kelulusan
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
