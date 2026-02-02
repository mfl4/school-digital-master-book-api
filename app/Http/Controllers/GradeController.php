<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Student;
use App\Services\GradeService;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

// Controller untuk mengelola nilai siswa (CRUD) dengan akses berdasarkan role (admin, guru, wali_kelas)
class GradeController extends Controller
{
    // Service untuk logic bisnis terkait grade
    protected $gradeService;

    // Inject GradeService melalui constructor
    public function __construct(GradeService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    // === ADMIN METHODS ===

    // Mengambil daftar nilai dengan filter (role-based: admin melihat semua, guru/walikelas hanya yang relevan)
    public function index(Request $request): JsonResponse
    {
        $grades = $this->gradeService->getGrades($request->all());
        return response()->json([
            'success' => true,
            'data' => $grades
        ]);
    }

    // Menampilkan detail nilai berdasarkan ID beserta relasi student dan subject
    public function show(int $id): JsonResponse
    {
        $grade = Grade::with(['student', 'subject', 'lastEditor'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $grade]);
    }

    // Menambahkan nilai baru ke database (validasi via request)
    public function store(StoreGradeRequest $request): JsonResponse
    {
        try {
            $grade = $this->gradeService->createGrade($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil ditambahkan',
                'data' => $grade->load(['student', 'subject'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    // Mengupdate nilai berdasarkan ID dengan validasi akses
    public function update(UpdateGradeRequest $request, Grade $grade): JsonResponse
    {
        try {
            $updatedGrade = $this->gradeService->updateGrade($grade, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil diperbarui',
                'data' => $updatedGrade->load(['student', 'subject'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 403);
        }
    }

    // Menghapus nilai (admin bisa hapus semua, guru hanya untuk mapel sendiri)
    public function destroy(Grade $grade): JsonResponse
    {
        $user = Auth::user();
        // Validasi: admin bisa hapus semua, guru hanya mata pelajaran sendiri
        if ($user->role !== 'admin') {
            if ($user->role === 'guru' && $user->subject != $grade->subject_id) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }
        }

        $grade->delete();
        return response()->json(['success' => true, 'message' => 'Nilai berhasil dihapus']);
    }

    // === GURU METHODS ===

    // Endpoint untuk guru mengambil nilai mata pelajaran yang diampu
    public function myGrades(Request $request): JsonResponse
    {
        // Logic filtering ada di service berdasarkan user role
        return $this->index($request);
    }

    // Guru menambahkan nilai untuk mata pelajaran yang diampu
    public function storeMyGrade(StoreGradeRequest $request): JsonResponse
    {
        return $this->store($request);
    }

    // Guru mengupdate nilai berdasarkan ID
    public function updateMyGrade(UpdateGradeRequest $request, int $id): JsonResponse
    {
        $grade = Grade::findOrFail($id);
        return $this->update($request, $grade);
    }

    // Guru menghapus nilai yang sudah diinput
    public function destroyMyGrade(Request $request, int $id): JsonResponse
    {
        $grade = Grade::findOrFail($id);
        return $this->destroy($grade);
    }

    // === WALI KELAS METHODS ===

    // Wali kelas mengambil nilai siswa di kelasnya
    public function classGrades(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    // Wali kelas menambahkan nilai untuk siswa di kelasnya
    public function storeClassGrade(StoreGradeRequest $request): JsonResponse
    {
        $user = Auth::user();
        if ($user->role !== 'wali_kelas') {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        // Validasi: siswa harus di kelas yang diampu wali kelas
        $student = Student::where('nis', $request->student_id)->first();
        if (!$student || !str_starts_with($student->rombel_absen, $user->class)) {
            return response()->json([
                'success' => false,
                'error' => 'Anda hanya bisa menginput nilai untuk siswa di kelas Anda',
            ], 403);
        }

        return $this->store($request);
    }

    // Wali kelas mengupdate nilai siswa di kelasnya
    public function updateClassGrade(UpdateGradeRequest $request, Grade $grade): JsonResponse
    {
        $user = Auth::user();
        if ($user->role !== 'wali_kelas') {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        // Validasi: nilai harus milik siswa di kelas yang diampu
        $student = Student::where('nis', $grade->student_id)->first();
        if (!$student || !str_starts_with($student->rombel_absen, $user->class)) {
            return response()->json([
                'success' => false,
                'error' => 'Anda hanya bisa mengubah nilai siswa di kelas Anda',
            ], 403);
        }

        return $this->update($request, $grade);
    }

    // Wali kelas menghapus nilai siswa di kelasnya
    public function destroyClassGrade(Grade $grade): JsonResponse
    {
        $user = Auth::user();
        if ($user->role !== 'wali_kelas') {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        // Validasi: nilai harus milik siswa di kelas yang diampu
        $student = Student::where('nis', $grade->student_id)->first();
        if (!$student || !str_starts_with($student->rombel_absen, $user->class)) {
            return response()->json([
                'success' => false,
                'error' => 'Anda hanya bisa menghapus nilai siswa di kelas Anda',
            ], 403);
        }

        return $this->destroy($grade);
    }
}
