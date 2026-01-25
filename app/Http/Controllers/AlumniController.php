<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * AlumniController
 *
 * Controller untuk mengelola data alumni (CRUD).
 * Admin: Full CRUD access
 * Alumni: Hanya bisa update data pribadi sendiri
 */
class AlumniController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * GET /api/alumni
     *
     * Query Parameters:
     * - search: Cari berdasarkan NIM, nama, atau email
     * - graduation_year: Filter berdasarkan tahun kelulusan
     * - employed: Filter alumni yang sudah bekerja (true/false)
     * - in_university: Filter alumni yang kuliah (true/false)
     * - per_page: Jumlah data per halaman (default: 15)
     */
    public function index(Request $request)
    {
        $query = Alumni::query();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by graduation year
        if ($request->filled('graduation_year')) {
            $query->byGraduationYear($request->graduation_year);
        }

        // Filter employed
        if ($request->has('employed') && $request->employed === 'true') {
            $query->employed();
        }

        // Filter in university
        if ($request->has('in_university') && $request->in_university === 'true') {
            $query->inUniversity();
        }

        // Order by graduation year desc, then name
        $query->orderBy('graduation_year', 'desc')->orderBy('name');

        // Pagination
        $perPage = $request->input('per_page', 15);
        $alumni = $query->paginate($perPage);

        return response()->json($alumni, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * POST /api/alumni
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:20|unique:alumni,nim',
            'name' => 'required|string|max:100',
            'graduation_year' => 'required|integer|min:1900|max:'.(date('Y') + 1),
            'university' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'job_start' => 'nullable|date|required_with:job_title',
            'job_end' => 'nullable|date|after:job_start',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'linkedin' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'nis' => 'nullable|string|max:20|exists:students,nis',
        ]);

        // Tambah tracking info
        $validated['updated_by'] = $request->user()->id;
        $validated['updated_ip'] = $request->ip();
        $validated['updated_at'] = now();
        $validated['created_at'] = now();

        $alumni = Alumni::create($validated);

        return response()->json([
            'message' => 'Data alumni berhasil ditambahkan.',
            'data' => $alumni,
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * GET /api/alumni/{nim}
     */
    public function show(Request $request, string $nim)
    {
        $alumni = Alumni::findOrFail($nim);

        // Alumni hanya bisa lihat data sendiri
        if ($request->user()->isAlumni()) {
            if ($request->user()->alumni !== $nim) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke data alumni ini.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        // Load relasi
        $alumni->load(['student', 'updater']);

        return response()->json($alumni, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * PUT /api/alumni/{nim}
     */
    public function update(Request $request, string $nim)
    {
        $alumni = Alumni::findOrFail($nim);

        // Alumni hanya bisa update data sendiri
        if ($request->user()->isAlumni()) {
            if ($request->user()->alumni !== $nim) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk mengedit data alumni ini.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'graduation_year' => 'sometimes|integer|min:1900|max:'.(date('Y') + 1),
            'university' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'job_start' => 'nullable|date',
            'job_end' => 'nullable|date|after:job_start',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'linkedin' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'nis' => 'nullable|string|max:20|exists:students,nis',
        ]);

        // Update tracking info
        $validated['updated_by'] = $request->user()->id;
        $validated['updated_ip'] = $request->ip();
        $validated['updated_at'] = now();

        // Simpan data lama untuk notifikasi
        $oldData = $alumni->toArray();

        $alumni->update($validated);

        // Jika alumni yang mengupdate data sendiri, kirim notifikasi ke admin
        if ($request->user()->isAlumni()) {
            $this->createNotificationForAdmin($alumni, $request->user(), $request->ip());
        }

        return response()->json([
            'message' => 'Data alumni berhasil diperbarui.',
            'data' => $alumni->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * DELETE /api/alumni/{nim}
     */
    public function destroy(Request $request, string $nim)
    {
        $alumni = Alumni::findOrFail($nim);

        // Hanya admin yang bisa hapus
        if (! $request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Hanya admin yang dapat menghapus data alumni.',
            ], Response::HTTP_FORBIDDEN);
        }

        $alumni->delete();

        return response()->json([
            'message' => 'Data alumni berhasil dihapus.',
        ], Response::HTTP_OK);
    }

    // =========================================================================
    // PUBLIC METHODS - Dapat diakses tanpa autentikasi
    // =========================================================================

    /**
     * List semua alumni untuk publik (dengan optional search)
     *
     * GET /api/public/alumni
     *
     * Query Parameters:
     * - search: Optional - Cari berdasarkan NIM atau nama (minimal 2 karakter)
     * - graduation_year: Optional - Filter berdasarkan tahun kelulusan
     * - per_page: Jumlah data per halaman (default: 20)
     */
    public function publicIndex(Request $request)
    {
        $query = Alumni::query();

        // Optional search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Optional graduation year filter
        if ($request->filled('graduation_year')) {
            $query->byGraduationYear($request->input('graduation_year'));
        }

        // Order by graduation year desc, then name
        $query->orderBy('graduation_year', 'desc')->orderBy('name');

        // Pagination
        $perPage = $request->input('per_page', 20);
        $alumni = $query->paginate($perPage);

        // Transform to public data
        $alumni->getCollection()->transform(function ($alumnus) {
            return $this->getPublicAlumniData($alumnus);
        });

        return response()->json([
            'success' => true,
            'message' => 'Data alumni berhasil diambil.',
            'data' => $alumni,
        ], Response::HTTP_OK);
    }

    /**
     * Pencarian langsung ke detail alumni berdasarkan NIM
     *
     * GET /api/public/alumni/search
     *
     * Query Parameters:
     * - q: NIM yang dicari (required, minimal 3 karakter)
     */
    public function publicSearch(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3|max:20',
        ]);

        $query = $request->input('q');

        /** @var Alumni|null $alumni */
        $alumni = Alumni::where('nim', $query)->first();

        if (! $alumni) {
            return response()->json([
                'success' => false,
                'message' => 'Data alumni tidak ditemukan.',
                'data' => null,
            ], Response::HTTP_NOT_FOUND);
        }

        // Hanya tampilkan data publik (terbatas)
        return response()->json([
            'success' => true,
            'message' => 'Data alumni ditemukan.',
            'data' => $this->getPublicAlumniData($alumni),
        ], Response::HTTP_OK);
    }

    /**
     * Menampilkan detail alumni untuk publik (data terbatas)
     *
     * GET /api/public/alumni/{nim}
     */
    public function publicShow(string $nim)
    {
        /** @var Alumni|null $alumni */
        $alumni = Alumni::find($nim);

        if (! $alumni) {
            return response()->json([
                'message' => 'Data alumni tidak ditemukan.',
                'data' => null,
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Data alumni ditemukan.',
            'data' => $this->getPublicAlumniData($alumni),
        ], Response::HTTP_OK);
    }

    /**
     * Menampilkan profil alumni yang sedang login
     *
     * GET /api/my-profile
     */
    public function myProfile(Request $request)
    {
        $nim = $request->user()->alumni;

        if (! $nim) {
            return response()->json([
                'message' => 'Akun Anda tidak terhubung dengan data alumni.',
            ], Response::HTTP_NOT_FOUND);
        }

        $alumni = Alumni::with(['student', 'updater'])->find($nim);

        if (! $alumni) {
            return response()->json([
                'message' => 'Data alumni tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($alumni, Response::HTTP_OK);
    }

    /**
     * Update profil alumni yang sedang login
     *
     * PATCH /api/my-profile
     */
    public function updateMyProfile(Request $request)
    {
        $nim = $request->user()->alumni;

        if (! $nim) {
            return response()->json([
                'message' => 'Akun Anda tidak terhubung dengan data alumni.',
            ], Response::HTTP_NOT_FOUND);
        }

        $alumni = Alumni::findOrFail($nim);

        $validated = $request->validate([
            'university' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'job_start' => 'nullable|date',
            'job_end' => 'nullable|date|after:job_start',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'linkedin' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        // Update tracking info
        $validated['updated_by'] = $request->user()->id;
        $validated['updated_ip'] = $request->ip();
        $validated['updated_at'] = now();

        $alumni->update($validated);

        // Kirim notifikasi ke admin
        $this->createNotificationForAdmin($alumni, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => $alumni->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Filter data alumni untuk ditampilkan ke publik
     * Hanya menampilkan data yang aman untuk publik
     */
    protected function getPublicAlumniData(Alumni $alumni): array
    {
        return [
            'nim' => $alumni->nim,
            'name' => $alumni->name,
            'graduation_year' => $alumni->graduation_year,
            'university' => $alumni->university,
            'job_title' => $alumni->job_title,
            // Data kontak sensitif tidak ditampilkan ke publik
        ];
    }

    /**
     * Buat notifikasi untuk admin ketika alumni mengupdate data
     */
    protected function createNotificationForAdmin(Alumni $alumni, $user, string $ip): void
    {
        // Cek apakah model Notification ada
        if (! class_exists(Notification::class)) {
            // Log atau skip jika model belum ada
            return;
        }

        try {
            Notification::create([
                'type' => 'alumni_update',
                'message' => "Alumni {$alumni->name} ({$alumni->nim}) telah memperbarui data pribadinya.",
                'triggered_by' => $user->id,
                'triggered_ip' => $ip,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            // Log error tapi jangan gagalkan proses
            Log::warning('Gagal membuat notifikasi: '.$e->getMessage());
        }
    }
}
