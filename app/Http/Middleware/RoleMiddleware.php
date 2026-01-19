<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Middleware untuk memvalidasi role pengguna.
     * Penggunaan: middleware('role:admin') atau middleware('role:admin,guru,wali_kelas')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Daftar role yang diizinkan (comma-separated)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Cek apakah user sudah terautentikasi
        if (!$user) {
            return response()->json([
                'message' => 'Anda harus login terlebih dahulu',
                'error' => 'unauthenticated'
            ], 401);
        }

        // Validasi role
        $allowedRoles = array_map('trim', $roles);

        if (!in_array($user->role, $allowedRoles, true)) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke resource ini',
                'error' => 'forbidden',
                'required_roles' => $allowedRoles,
                'your_role' => $user->role
            ], 403);
        }

        return $next($request);
    }
}
