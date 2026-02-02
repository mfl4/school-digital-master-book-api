<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

// Controller untuk mengelola data alumni dengan CRUD lengkap dan endpoint publik
class AlumniController extends Controller
{
    // Mengambil daftar alumni dengan filter: search, graduation_year, employed, in_university
    public function index(Request $request)
    {
        $query = Alumni::query();

        // Filter pencarian berdasarkan NIM, nama, atau email
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter berdasarkan tahun kelulusan
        if ($request->filled('graduation_year')) {
            $query->byGraduationYear($request->graduation_year);
        }

        // Filter alumni yang sudah bekerja
        if ($request->has('employed') && $request->employed === 'true') {
            $query->employed();
        }

        // Filter alumni yang masih kuliah
        if ($request->has('in_university') && $request->in_university === 'true') {
            $query->inUniversity();
        }

        // Urutkan berdasarkan tahun kelulusan dan nama
        $query->orderBy('graduation_year', 'desc')->orderBy('name');

        // Pagination dengan default 15 item per page
        $perPage = $request->input('per_page', 15);
        $alumni = $query->paginate($perPage);

        return response()->json($alumni, Response::HTTP_OK);
    }

    // Menambahkan data alumni baru (hanya admin)
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

        // Normalisasi input: ubah string kosong menjadi null untuk PostgreSQL
        $validated = $this->normalizeInput($validated);

        // Tambahkan tracking info untuk audit
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

    // Menampilkan detail alumni berdasarkan NIM dengan validasi akses
    public function show(Request $request, string $nim)
    {
        $alumni = Alumni::findOrFail($nim);

        // Validasi: alumni hanya bisa melihat data milik sendiri
        if ($request->user()->isAlumni()) {
            if ($request->user()->alumni !== $nim) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke data alumni ini.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        // Muat relasi student dan updater untuk informasi lengkap
        $alumni->load(['student', 'updater']);

        return response()->json($alumni, Response::HTTP_OK);
    }

    // Mengupdate data alumni dengan notifikasi ke admin jika alumni yang mengupdate
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

        // Normalisasi input untuk PostgreSQL
        $validated = $this->normalizeInput($validated);

        // Update tracking info untuk audit trail
        $validated['updated_by'] = $request->user()->id;
        $validated['updated_ip'] = $request->ip();
        $validated['updated_at'] = now();

        // Simpan data lama untuk keperluan notifikasi (opsional)
        $oldData = $alumni->toArray();

        $alumni->update($validated);

        // Buat notifikasi ke admin jika alumni yang mengupdate sendiri
        if ($request->user()->isAlumni()) {
            $this->createNotificationForAdmin($alumni, $request->user(), $request->ip());
        }

        return response()->json([
            'message' => 'Data alumni berhasil diperbarui.',
            'data' => $alumni->fresh(),
        ], Response::HTTP_OK);
    }

    // Menghapus data alumni (hanya admin)
    public function destroy(Request $request, string $nim)
    {
        $alumni = Alumni::findOrFail($nim);

        // Validasi: hanya admin yang boleh menghapus data alumni
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

    // [PUBLIC] Endpoint publik untuk mengambil daftar alumni dengan filter
    public function publicIndex(Request $request)
    {
        $query = Alumni::query();

        // Filter pencarian opsional berdasarkan NIM atau nama
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter opsional berdasarkan tahun lulus
        if ($request->filled('graduation_year')) {
            $query->byGraduationYear($request->input('graduation_year'));
        }

        // Urutkan berdasarkan tahun lulus (terbaru) lalu nama
        $query->orderBy('graduation_year', 'desc')->orderBy('name');

        // Paginasi
        $perPage = $request->input('per_page', 20);
        $alumni = $query->paginate($perPage);

        // Transformasi ke format data publik
        $alumni->getCollection()->transform(function ($alumnus) {
            return $this->getPublicAlumniData($alumnus);
        });

        return response()->json([
            'success' => true,
            'message' => 'Data alumni berhasil diambil.',
            'data' => $alumni,
        ], Response::HTTP_OK);
    }

    // [PUBLIC] Pencarian langsung berdasarkan NIM (untuk scanning atau quick search)
    public function publicSearch(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3|max:20',
        ]);

        $query = $request->input('q');

        // Cari alumni berdasarkan NIM (pencarian persis)
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

    // [PUBLIC] Endpoint publik untuk menampilkan detail alumni berdasarkan NIM
    public function publicShow(string $nim)
    {
        // Cari alumni berdasarkan NIM
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

    // Endpoint untuk alumni yang sedang login mengakses profil sendiri
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

    // Endpoint untuk alumni update profil sendiri dengan notifikasi ke admin
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

        // Normalisasi input untuk PostgreSQL
        $validated = $this->normalizeInput($validated);

        // Tracking: siapa dan kapan update
        $validated['updated_by'] = $request->user()->id;
        $validated['updated_ip'] = $request->ip();
        $validated['updated_at'] = now();

        $alumni->update($validated);

        // Notifikasi ke admin bahwa alumni update profil
        $this->createNotificationForAdmin($alumni, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => $alumni->fresh(),
        ], Response::HTTP_OK);
    }

    // Helper method: filter data alumni untuk ditampilkan ke publik (data sensitif dihilangkan)
    protected function getPublicAlumniData(Alumni $alumni): array
    {
        return [
            'nim' => $alumni->nim,
            'name' => $alumni->name,
            'graduation_year' => $alumni->graduation_year,
            'university' => $alumni->university,
            'job_title' => $alumni->job_title,
            // Email, phone, dan data kontak lain tidak ditampilkan untuk privasi
        ];
    }

    // Helper method: buat notifikasi untuk admin ketika alumni update data
    protected function createNotificationForAdmin(Alumni $alumni, $user, string $ip): void
    {
        // Validasi apakah model Notification tersedia
        if (! class_exists(Notification::class)) {
            // Skip jika model belum ada di database
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
            // Log error tapi jangan gagalkan request utama
            Log::warning('Gagal membuat notifikasi: '.$e->getMessage());
        }
    }

    // Helper method: normalisasi input untuk PostgreSQL (empty string -> null)
    protected function normalizeInput(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }
        return $data;
    }
}
