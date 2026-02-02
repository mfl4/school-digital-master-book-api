<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

// Controller untuk menangani autentikasi pengguna (login, logout, current user)
class AuthController extends Controller
{
    // Method untuk login user: validasi kredensial dan buat session
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($validated)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Muat relasi subject dan alumni untuk user yang berhasil login
            $user->load('subjectRelation', 'alumniRelation');

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'subject' => $user->subjectRelation,
                    'class' => $user->class,
                    'alumni' => $user->alumniRelation,
                ]
            ]);
        }

        return response()->json([
            'message' => 'Email atau password salah'
        ], 401);
    }

    // Method untuk logout user: hapus session dan regenerate token untuk keamanan
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    // Method untuk mengambil data user yang sedang login beserta relasi subject dan alumni
    public function currentUser(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
             return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Muat relasi untuk ditampilkan di frontend
        $user->load('subjectRelation', 'alumniRelation');
        
        return response()->json([
            'message' => 'Data user berhasil diambil',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'subject' => $user->subjectRelation,
                'class' => $user->class,
                'alumni' => $user->alumniRelation,
            ]
        ]);
    }
}
