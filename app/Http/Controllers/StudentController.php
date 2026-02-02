<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\StudentService;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Controller untuk mengelola data siswa (CRUD) dengan akses berbasis role
class StudentController extends Controller
{
    // Service untuk logic bisnis terkait student
    protected $studentService;

    // Inject StudentService untuk digunakan di semua method
    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    // Mengambil daftar siswa dengan filter dan pagination
    public function index(Request $request): JsonResponse
    {
        $students = $this->studentService->getStudents($request->all());
        return response()->json($students);
    }

    // Menambahkan data siswa baru ke database
    public function store(StoreStudentRequest $request): JsonResponse
    {
        try {
            $student = $this->studentService->createStudent($request->validated());
            return response()->json([
                'message' => 'Data siswa berhasil ditambahkan.',
                'data' => $student,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Menampilkan detail data siswa berdasarkan NIS dengan validasi akses wali kelas
    public function show(Request $request, string $nis): JsonResponse
    {
        $student = Student::with('lastEditor')->findOrFail($nis);

        // Validasi: wali kelas hanya bisa lihat siswa di kelasnya
        if ($request->user()->isWaliKelas()) {
            if ($student->class !== $request->user()->class) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        return response()->json($student);
    }

    // Mengupdate data siswa berdasarkan NIS
    public function update(UpdateStudentRequest $request, string $nis): JsonResponse
    {
        try {
            $student = $this->studentService->updateStudent($nis, $request->validated());
            return response()->json([
                'message' => 'Data siswa berhasil diperbarui.',
                'data' => $student->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    // Menghapus data siswa (hanya admin yang boleh)
    public function destroy(Request $request, string $nis): JsonResponse
    {
        // Validasi: hanya admin yang bisa hapus data siswa
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->studentService->deleteStudent($nis);
        return response()->json(['message' => 'Data siswa berhasil dihapus.']);
    }

    // Endpoint publik untuk mengambil daftar siswa dengan filter (tanpa autentikasi)
    public function publicIndex(Request $request)
    {
        $query = Student::query();
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('class')) {
            $query->byClass($request->input('class'));
        }
        $query->orderBy('rombel_absen');

        $students = $query->paginate($request->input('per_page', 20));

        // Transform data siswa menjadi format publik yang aman
        $students->getCollection()->transform(function ($student) {
            return $this->getPublicStudentData($student);
        });

        return response()->json([
            'success' => true,
            'message' => 'Data siswa berhasil diambil.',
            'data' => $students
        ]);
    }

    // Endpoint publik untuk pencarian siswa berdasarkan NIS atau NISN
    public function publicSearch(Request $request)
    {
        $request->validate(['q' => 'required|string|min:3|max:20']);
        $q = $request->input('q');
        $student = Student::where('nis', $q)->orWhere('nisn', $q)->first();

        if (!$student) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        return response()->json([
            'success' => true,
            'message' => 'Found',
            'data' => $this->getPublicStudentData($student)
        ]);
    }

    // Endpoint publik untuk menampilkan detail siswa berdasarkan NIS
    public function publicShow(string $nis)
    {
        $student = Student::find($nis);
        if (!$student) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['message' => 'Found', 'data' => $this->getPublicStudentData($student)]);
    }

    // Helper method untuk filter data siswa yang aman ditampilkan ke publik
    protected function getPublicStudentData(Student $student): array
    {
        return [
            'nis' => $student->nis,
            'nisn' => $student->nisn,
            'name' => $student->name,
            'gender' => $student->gender,
            'gender_label' => $student->gender_label,
            'birth_place' => $student->birth_place,
            'birth_date' => $student->birth_date->format('Y-m-d'),
            'religion' => $student->religion,
            'rombel_absen' => $student->rombel_absen,
            'class' => $student->class,
        ];
    }
}
