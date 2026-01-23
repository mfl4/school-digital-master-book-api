<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * AuthController
 * 
 * Controller untuk menangani autentikasi pengguna:
 * - Login: Autentikasi dan generate token
 * - Logout: Hapus token aktif
 * - Current User: Ambil data pengguna yang sedang login
 */
class AuthController extends Controller
{
    /**
     * Login user dan generate token Sanctum
     * 
     * Proses:
     * 1. Validasi input email dan password
     * 2. Cari user berdasarkan email
     * 3. Verifikasi password dengan Hash::check()
     * 4. Generate token baru menggunakan Sanctum
     * 5. Kembalikan response dengan token dan data user
     */

    public function login(Request $request): JsonResponse
    {
        // Validasi input dengan pesan error dalam Bahasa Indonesia
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6']
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter'
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $validated['email'])->first();

        // Validasi ketika user tidak ditemukan ATAU password tidak cocok
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Generate token dengan nama unik
        $tokenName = 'auth_token_' . $user->id . '_' . now()->timestamp;
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'subject' => $user->subject,
                'class' => $user->class,
                'alumni' => $user->alumni,
            ]
        ]);
    }

    /**
     * Logout user dan hapus token saat ini
     * 
     * Proses:
     * 1. Ambil token yang sedang digunakan via currentAccessToken()
     * 2. Hapus token tersebut dari database
     * 3. Kembalikan response sukses
     */

    public function logout(Request $request): JsonResponse
    {
        // Hapus token saat ini
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get data user yang sedang login
     * 
     * Proses:
     * 1. Ambil user dari request (sudah di-inject oleh middleware auth:sanctum)
     * 2. Kembalikan data user lengkap
     */

    public function currentUser(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Data user berhasil diambil',
            'data' => $request->user()
        ]);
    }
}
