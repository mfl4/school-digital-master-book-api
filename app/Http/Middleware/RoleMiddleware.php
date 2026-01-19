<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware
 * 
 * Middleware untuk memvalidasi role pengguna.
 * Setiap user HANYA memiliki SATU role, sehingga pengecekan sederhana.
 */
class RoleMiddleware
{
    /**
     * Handle incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @param string $role Role yang diizinkan mengakses route
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // Cek apakah user sudah login
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        // Cek apakah role user sesuai dengan yang diizinkan
        if ($user->role !== $role) {
            return response()->json([
                'message' => 'Forbidden.',
                'error' => 'Anda tidak memiliki akses ke halaman ini',
                'required_role' => $role,
                'your_role' => $user->role
            ], 403);
        }

        return $next($request);
    }
}
