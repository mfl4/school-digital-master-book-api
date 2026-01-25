<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * UserController
 * 
 * Controller untuk mengelola data user (CRUD).
 * Hanya dapat diakses oleh Admin.
 */
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * GET /api/users
     * 
     * Query Parameters:
     * - search: Cari berdasarkan nama atau email
     * - role: Filter berdasarkan role
     * - per_page: Jumlah data per halaman (default: 15)
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Load relasi untuk ditampilkan di frontend
        $query->with(['subjectRelation', 'alumniRelation']);

        // Order by name
        $query->orderBy('name');

        // Pagination
        $perPage = $request->input('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json($users, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * POST /api/users
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:' . implode(',', User::ROLES),
            'subject' => 'nullable|integer|exists:subjects,id|required_if:role,guru',
            'class' => 'nullable|string|max:10|required_if:role,wali_kelas',
            'alumni' => 'nullable|string|max:20|exists:alumni,nim|required_if:role,alumni',
        ]);

        // Sanitize based on role
        $validated = $this->sanitizeByRole($validated);

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        
        // Load relasi setelah create
        $user->load(['subjectRelation', 'alumniRelation']);

        return response()->json([
            'message' => 'User berhasil ditambahkan.',
            'data' => $user,
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     * 
     * GET /api/users/{id}
     */
    public function show(User $user)
    {
        // Load relasi untuk detail user
        $user->load(['subjectRelation', 'alumniRelation']);
        
        return response()->json($user, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     * 
     * PUT /api/users/{id}
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|in:' . implode(',', User::ROLES),
            'subject' => 'nullable|integer|exists:subjects,id',
            'class' => 'nullable|string|max:10',
            'alumni' => 'nullable|string|max:20|exists:alumni,nim',
        ]);

        // Jika role berubah, sanitize fields
        if (isset($validated['role'])) {
            $validated = $this->sanitizeByRole($validated);
        }

        // Hash password jika ada
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        
        // Refresh dan load relasi
        $user = $user->fresh(['subjectRelation', 'alumniRelation']);

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'data' => $user,
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * DELETE /api/users/{id}
     */
    public function destroy(Request $request, User $user)
    {
        // Tidak bisa hapus diri sendiri
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Tidak dapat menghapus akun sendiri.',
            ], Response::HTTP_FORBIDDEN);
        }

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ], Response::HTTP_OK);
    }

    /**
     * Sanitize fields berdasarkan role
     * - Guru: hanya subject yang diisi
     * - Wali Kelas: hanya class yang diisi
     * - Alumni: hanya alumni yang diisi
     * - Admin: semua null
     */
    protected function sanitizeByRole(array $data): array
    {
        $role = $data['role'] ?? null;

        switch ($role) {
            case 'admin':
                $data['subject'] = null;
                $data['class'] = null;
                $data['alumni'] = null;
                break;

            case 'guru':
                $data['class'] = null;
                $data['alumni'] = null;
                break;

            case 'wali_kelas':
                $data['subject'] = null;
                $data['alumni'] = null;
                break;

            case 'alumni':
                $data['subject'] = null;
                $data['class'] = null;
                break;
        }

        return $data;
    }
}
