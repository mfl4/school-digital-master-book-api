<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

// Controller untuk mengelola user (CRUD) khusus untuk admin (create, read, update, delete users)
class UserController extends Controller
{
    // Mengambil daftar user dengan filter search dan role, serta pagination
    public function index(Request $request)
    {
        $query = User::query();

        // Filter pencarian berdasarkan nama atau email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        // Filter berdasarkan role (admin, guru, wali_kelas, alumni)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Muat relasi subject dan alumni untuk ditampilkan di frontend
        $query->with(['subjectRelation', 'alumniRelation']);

        // Urutkan berdasarkan nama
        $query->orderBy('name');

        // Pagination dengan default 15 items per page
        $perPage = $request->input('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json($users, Response::HTTP_OK);
    }

    // Menambahkan user baru dengan role dan field spesifik sesuai role
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

        // Sanitasi field berdasarkan role (guru->subject, wali_kelas->class, alumni->alumni)
        $validated = $this->sanitizeByRole($validated);

        // Hash password sebelum disimpan ke database
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        
        // Muat relasi setelah create agar response lengkap
        $user->load(['subjectRelation', 'alumniRelation']);

        return response()->json([
            'message' => 'User berhasil ditambahkan.',
            'data' => $user,
        ], Response::HTTP_CREATED);
    }

    // Menampilkan detail user berdasarkan ID beserta relasinya
    public function show(User $user)
    {
        // Muat relasi untuk detail user lengkap
        $user->load(['subjectRelation', 'alumniRelation']);
        
        return response()->json($user, Response::HTTP_OK);
    }

    // Mengupdate data user dengan validasi dan sanitasi berdasarkan role
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

        // Jika role berubah, sanitasi field yang tidak relevan
        if (isset($validated['role'])) {
            $validated = $this->sanitizeByRole($validated);
        }

        // Hash password baru jika diubah
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        
        // Refresh model dan muat relasi terbaru
        $user = $user->fresh(['subjectRelation', 'alumniRelation']);

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'data' => $user,
        ], Response::HTTP_OK);
    }

    // Menghapus user dari database (tidak bisa hapus diri sendiri)
    public function destroy(Request $request, User $user)
    {
        // Validasi: user tidak bisa menghapus akun sendiri
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

    // Helper method: sanitasi field berdasarkan role user
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
