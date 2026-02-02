<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Middleware untuk validasi role pengguna (RBAC - Role-Based Access Control)
class RoleMiddleware
{
    // Handle incoming request dan cek apakah user memiliki role yang diizinkan
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        // Cek apakah user sudah login
        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Silakan login terlebih dahulu',
            ], 401);
        }

        // Bisa akses lebih dari 1 role
        $allowedRoles = explode('|', $roles);

        // Cek apakah role user ada dalam daftar yang diizinkan
        if (! in_array($user->role, $allowedRoles)) {
            return response()->json([
                'message' => 'Forbidden.',
                'error' => 'Anda tidak memiliki akses ke halaman ini',
                'required_role' => $roles,
                'your_role' => $user->role,
            ], 403);
        }

        return $next($request);
    }
}
