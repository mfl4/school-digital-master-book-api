<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * StudentController
 * 
 * Controller untuk mengelola data siswa (CRUD).
 * Hanya dapat diakses oleh Admin dan Wali Kelas.
 */
class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * GET /api/students
     * 
     * Query Parameters:
     * - search: Cari berdasarkan NIS, NISN, atau nama
     * - class: Filter berdasarkan kelas (contoh: X-1)
     * - gender: Filter berdasarkan jenis kelamin (L/P)
     * - per_page: Jumlah data per halaman (default: 15)
     */
    public function index(Request $request)
    {
        $query = Student::query();

        // Filter berdasarkan kelas untuk wali kelas
        if ($request->user()->isWaliKelas()) {
            $query->byClass($request->user()->class);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by class (hanya admin yang bisa filter semua kelas)
        if ($request->filled('class') && $request->user()->isAdmin()) {
            $query->byClass($request->class);
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        // Order by rombel_absen
        $query->orderBy('rombel_absen')->orderBy('name');

        // Pagination
        $perPage = $request->input('per_page', 15);
        $students = $query->paginate($perPage);

        return response()->json($students, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * POST /api/students
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:20|unique:students,nis',
            'nisn' => 'required|string|max:20|unique:students,nisn',
            'name' => 'required|string|max:100',
            'gender' => 'required|in:L,P',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'religion' => 'required|in:' . implode(',', Student::RELIGIONS),
            'father_name' => 'required|string|max:100',
            'address' => 'required|string',
            'ijazah_number' => 'nullable|string|max:50',
            'rombel_absen' => 'required|string|max:10|regex:/^(X|XI|XII)-\d+-\d+$/',
        ]);

        // Tambah tracking info
        $validated['last_edited_by'] = $request->user()->id;
        $validated['last_edited_ip'] = $request->ip();
        $validated['last_edited_at'] = now();

        $student = Student::create($validated);

        return response()->json([
            'message' => 'Data siswa berhasil ditambahkan.',
            'data' => $student,
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     * 
     * GET /api/students/{nis}
     */
    public function show(Request $request, string $nis)
    {
        $student = Student::findOrFail($nis);

        // Wali kelas hanya bisa lihat siswa di kelasnya
        if ($request->user()->isWaliKelas()) {
            if ($student->class !== $request->user()->class) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke data siswa ini.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        // Load relasi
        $student->load('lastEditor');

        return response()->json($student, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     * 
     * PUT /api/students/{nis}
     */
    public function update(Request $request, string $nis)
    {
        $student = Student::findOrFail($nis);

        // Wali kelas hanya bisa edit siswa di kelasnya
        if ($request->user()->isWaliKelas()) {
            if ($student->class !== $request->user()->class) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk mengedit data siswa ini.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $validated = $request->validate([
            'nisn' => 'sometimes|string|max:20|unique:students,nisn,' . $nis . ',nis',
            'name' => 'sometimes|string|max:100',
            'gender' => 'sometimes|in:L,P',
            'birth_place' => 'sometimes|string|max:50',
            'birth_date' => 'sometimes|date',
            'religion' => 'sometimes|in:' . implode(',', Student::RELIGIONS),
            'father_name' => 'sometimes|string|max:100',
            'address' => 'sometimes|string',
            'ijazah_number' => 'nullable|string|max:50',
            'rombel_absen' => 'sometimes|string|max:10|regex:/^(X|XI|XII)-\d+-\d+$/',
        ]);

        // Update tracking info
        $validated['last_edited_by'] = $request->user()->id;
        $validated['last_edited_ip'] = $request->ip();
        $validated['last_edited_at'] = now();

        $student->update($validated);

        return response()->json([
            'message' => 'Data siswa berhasil diperbarui.',
            'data' => $student->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * DELETE /api/students/{nis}
     */
    public function destroy(Request $request, string $nis)
    {
        $student = Student::findOrFail($nis);

        // Hanya admin yang bisa hapus
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Hanya admin yang dapat menghapus data siswa.',
            ], Response::HTTP_FORBIDDEN);
        }

        $student->delete();

        return response()->json([
            'message' => 'Data siswa berhasil dihapus.',
        ], Response::HTTP_OK);
    }

    // =========================================================================
    // PUBLIC METHODS - Dapat diakses tanpa autentikasi
    // =========================================================================

    /**
     * Pencarian data siswa untuk publik
     * 
     * GET /api/public/students/search
     * 
     * Query Parameters:
     * - q: NIS atau NISN yang dicari (required, minimal 3 karakter)
     */
    public function publicSearch(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3|max:20',
        ]);

        $query = $request->input('q');

        /** @var Student|null $student */
        $student = Student::where('nis', $query)
            ->orWhere('nisn', $query)
            ->first();

        if (!$student) {
            return response()->json([
                'message' => 'Data siswa tidak ditemukan.',
                'data' => null,
            ], Response::HTTP_NOT_FOUND);
        }

        // Hanya tampilkan data publik (terbatas)
        return response()->json([
            'message' => 'Data siswa ditemukan.',
            'data' => $this->getPublicStudentData($student),
        ], Response::HTTP_OK);
    }

    /**
     * Menampilkan detail siswa untuk publik (data terbatas)
     * 
     * GET /api/public/students/{nis}
     */
    public function publicShow(string $nis)
    {
        /** @var Student|null $student */
        $student = Student::find($nis);

        if (!$student) {
            return response()->json([
                'message' => 'Data siswa tidak ditemukan.',
                'data' => null,
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Data siswa ditemukan.',
            'data' => $this->getPublicStudentData($student),
        ], Response::HTTP_OK);
    }

    /**
     * Filter data siswa untuk ditampilkan ke publik
     * Hanya menampilkan data yang aman untuk publik
     */
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
