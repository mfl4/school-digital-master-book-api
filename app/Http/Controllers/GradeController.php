<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;use Illuminate\Validation\Rule;

/**
 * GradeController
 * 
 * Controller untuk mengelola data nilai raport (CRUD Grades).
 * 
 * Role-based access:
 * - Admin: Full CRUD access untuk semua grades
 * - Guru: Hanya bisa CRUD grades untuk mapel yang diampu
 * - Wali Kelas: Bisa CRUD grades untuk semua mapel siswa di kelasnya
 */
class GradeController extends Controller
{
    // =========================================================================
    // ADMIN METHODS
    // =========================================================================

    /**
     * Display a listing of grades (Admin Only)
     * 
     * GET /api/grades
     * 
     * Query Parameters:
     * - student_id: Filter by student NIS
     * - subject_id: Filter by subject ID
     * - semester: Filter by semester
     * - per_page: Jumlah data per halaman (default: 15)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Grade::with(['student', 'subject', 'lastEditor']);

        // Filter by student
        if ($request->filled('student_id')) {
            $query->byStudent($request->student_id);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->bySubject($request->subject_id);
        }

        // Filter by semester
        if ($request->filled('semester')) {
            $query->bySemester($request->semester);
        }

        $grades = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Data grades berhasil diambil',
            'data' => $grades,
        ]);
    }

    /**
     * Store a newly created grade (Admin Only)
     * 
     * POST /api/grades
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,nis'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'semester' => ['required', 'string', 'max:50'],
            'score' => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            'student_id.required' => 'NIS siswa wajib diisi',
            'student_id.exists' => 'Siswa tidak ditemukan',
            'subject_id.required' => 'Mata pelajaran wajib dipilih',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan',
            'semester.required' => 'Semester wajib diisi',
            'score.required' => 'Nilai wajib diisi',
            'score.integer' => 'Nilai harus berupa angka',
            'score.min' => 'Nilai minimal 0',
            'score.max' => 'Nilai maksimal 100',
        ]);

        // Auto set tracking fields
        $validated['last_edited_by'] = $request->user()->id;
        $validated['last_edited_ip'] = $request->ip();
        $validated['last_edited_at'] = now();

        $grade = Grade::create($validated);
        $grade->load(['student', 'subject']);

        return response()->json([
            'success' => true,
            'message' => 'Grade berhasil ditambahkan',
            'data' => $grade,
        ], 201);
    }

    /**
     * Display the specified grade
     * 
     * GET /api/grades/{id}
     */
    public function show(int $id): JsonResponse
    {
        $grade = Grade::with(['student', 'subject', 'lastEditor'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $grade,
        ]);
    }

    /**
     * Update the specified grade (Admin Only)
     * 
     * PATCH /api/grades/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $grade = Grade::findOrFail($id);

        $validated = $request->validate([
            'student_id' => ['sometimes', 'exists:students,nis'],
            'subject_id' => ['sometimes', 'exists:subjects,id'],
            'semester' => ['sometimes', 'string', 'max:50'],
            'score' => ['sometimes', 'integer', 'min:0', 'max:100'],
        ], [
            'student_id.exists' => 'Siswa tidak ditemukan',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan',
            'score.integer' => 'Nilai harus berupa angka',
            'score.min' => 'Nilai minimal 0',
            'score.max' => 'Nilai maksimal 100',
        ]);

        // Auto update tracking fields
        $validated['last_edited_by'] = $request->user()->id;
        $validated['last_edited_ip'] = $request->ip();
        $validated['last_edited_at'] = now();

        $grade->update($validated);
        $grade->load(['student', 'subject']);

        return response()->json([
            'success' => true,
            'message' => 'Grade berhasil diupdate',
            'data' => $grade,
        ]);
    }

    /**
     * Remove the specified grade (Admin Only)
     * 
     * DELETE /api/grades/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $grade = Grade::findOrFail($id);
        $grade->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grade berhasil dihapus',
        ]);
    }

    // =========================================================================
    // GURU METHODS
    // =========================================================================

    /**
     * Display grades yang di-input oleh guru (Guru Only)
     * 
     * GET /api/my-grades
     */
    public function myGrades(Request $request): JsonResponse
    {
        $user = $request->user();

        // Guru hanya bisa lihat grades yang dia input sendiri untuk mapel yang diampu
        $query = Grade::with(['student', 'subject'])
            ->where('subject_id', $user->subject)
           ->where('last_edited_by', $user->id);

        // Filter by semester
        if ($request->filled('semester')) {
            $query->bySemester($request->semester);
        }

        $grades = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Data grades berhasil diambil',
            'data' => $grades,
        ]);
    }

    /**
     * Input nilai oleh guru (Guru Only)
     * Guru hanya bisa input nilai untuk mapel yang diampu
     * 
     * POST /api/my-grades
     */
    public function storeMyGrade(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,nis'],
            'semester' => ['required', 'string', 'max:50'],
            'score' => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            'student_id.required' => 'NIS siswa wajib diisi',
            'student_id.exists' => 'Siswa tidak ditemukan',
            'semester.required' => 'Semester wajib diisi',
            'score.required' => 'Nilai wajib diisi',
            'score.integer' => 'Nilai harus berupa angka',
            'score.min' => 'Nilai minimal 0',
            'score.max' => 'Nilai maksimal 100',
        ]);

        // Auto set subject_id dari guru yang login
        $validated['subject_id'] = $user->subject;

        // Auto set tracking fields
        $validated['last_edited_by'] = $user->id;
        $validated['last_edited_ip'] = $request->ip();
        $validated['last_edited_at'] = now();

        $grade = Grade::create($validated);
        $grade->load(['student', 'subject']);

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil ditambahkan',
            'data' => $grade,
        ], 201);
    }

    /**
     * Update nilai oleh guru (Guru Only)
     * Guru hanya bisa update nilai yang dia input sendiri
     * 
     * PATCH /api/my-grades/{id}
     */
    public function updateMyGrade(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $grade = Grade::findOrFail($id);

        // Validasi: Guru hanya bisa update grade yang dia input sendiri
        if ($grade->last_edited_by !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Anda hanya bisa mengupdate nilai yang Anda input sendiri',
            ], 403);
        }

        // Validasi: Guru hanya bisa update grade untuk mapel yang diampu
        if ($grade->subject_id !== $user->subject) {
            return response()->json([
                'success' => false,
                'error' => 'Anda hanya bisa mengupdate nilai untuk mata pelajaran yang Anda ampu',
            ], 403);
        }

        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            'score.required' => 'Nilai wajib diisi',
            'score.integer' => 'Nilai harus berupa angka',
            'score.min' => 'Nilai minimal 0',
            'score.max' => 'Nilai maksimal 100',
        ]);

        // Auto update tracking fields
        $validated['last_edited_by'] = $user->id;
        $validated['last_edited_ip'] = $request->ip();
        $validated['last_edited_at'] = now();

        $grade->update($validated);
        $grade->load(['student', 'subject']);

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil diupdate',
            'data' => $grade,
        ]);
    }

    /**
     * Delete nilai oleh guru (Guru Only)
     * Guru hanya bisa delete nilai yang dia input sendiri
     * 
     * DELETE /api/my-grades/{id}
     */
    public function destroyMyGrade(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $grade = Grade::findOrFail($id);

        // Validasi: Guru hanya bisa delete grade yang dia input sendiri
        if ($grade->last_edited_by !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Anda hanya bisa menghapus nilai yang Anda input sendiri',
            ], 403);
        }

        $grade->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil dihapus',
        ]);
    }

    // =========================================================================
    // WALI KELAS METHODS
    // =========================================================================

    /**
     * Display grades siswa di kelas wali kelas (Wali Kelas Only)
     * 
     * GET /api/wali/grades
     */
    public function classGrades(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get grades untuk siswa di kelas wali kelas
        $query = Grade::with(['student', 'subject', 'lastEditor'])
            ->byClass($user->class);

        // Filter by semester
        if ($request->filled('semester')) {
            $query->bySemester($request->semester);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->bySubject($request->subject_id);
        }

        $grades = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Data grades berhasil diambil',
            'data' => $grades,
        ]);
    }

    /**
     * Input nilai untuk siswa di kelas (Wali Kelas Only)
     * Wali kelas bisa input nilai untuk semua mapel siswa di kelasnya
     * 
     * POST /api/wali/grades
     */
    public function storeClassGrade(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,nis'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'semester' => ['required', 'string', 'max:50'],
            'score' => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            'student_id.required' => 'NIS siswa wajib diisi',
            'student_id.exists' => 'Siswa tidak ditemukan',
            'subject_id.required' => 'Mata pelajaran wajib dipilih',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan',
            'semester.required' => 'Semester wajib diisi',
            'score.required' => 'Nilai wajib diisi',
            'score.integer' => 'Nilai harus berupa angka',
            'score.min' => 'Nilai minimal 0',
            'score.max' => 'Nilai maksimal 100',
        ]);

        // Validasi: Wali kelas hanya bisa input nilai untuk siswa di kelasnya
        $student = Student::findOrFail($validated['student_id']);
        if (!str_starts_with($student->rombel_absen, $user->class . '-')) {
            return response()->json([
                'success' => false,
                'error' => 'Anda hanya bisa menginput nilai untuk siswa di kelas Anda',
            ], 403);
        }

        // Auto set tracking fields
        $validated['last_edited_by'] = $user->id;
        $validated['last_edited_ip'] = $request->ip();
        $validated['last_edited_at'] = now();

        $grade = Grade::create($validated);
        $grade->load(['student', 'subject']);

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil ditambahkan',
            'data' => $grade,
        ], 201);
    }
}
