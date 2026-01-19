<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Route untuk API Buku Induk Digital
| Prefix: /api
|
*/

// ============================================================================
// PUBLIC ROUTES (Tidak perlu autentikasi)
// ============================================================================

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

// ============================================================================
// AUTHENTICATED ROUTES (Perlu login)
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {

    // --- Authentication Routes ---
    Route::prefix('')->group(function () {
        Route::get('/current-user', [AuthController::class, 'currentUser'])->name('auth.current-user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
        Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('auth.refresh-token');
    });

    // ========================================================================
    // ADMIN ONLY ROUTES
    // ========================================================================

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Test endpoint untuk admin
        Route::get('/test', function () {
            return response()->json([
                'message' => 'Selamat datang Admin!',
                'user' => request()->user()->only(['id', 'name', 'email', 'role'])
            ]);
        })->name('test');

        // Tambahkan route admin lainnya di sini
        // Route::apiResource('users', UserController::class);
        // Route::apiResource('students', StudentController::class);
        // Route::apiResource('alumni', AlumniController::class);
    });

    // ========================================================================
    // TEACHER ROUTES (Guru & Wali Kelas)
    // ========================================================================

    Route::middleware('role:guru,wali_kelas,admin')->prefix('teacher')->name('teacher.')->group(function () {
        // Test endpoint untuk guru/wali kelas
        Route::get('/test', function () {
            return response()->json([
                'message' => 'Selamat datang Guru/Wali Kelas!',
                'user' => request()->user()->only(['id', 'name', 'email', 'role', 'subject', 'class'])
            ]);
        })->name('test');

        // Tambahkan route teacher lainnya di sini
        // Route::apiResource('grades', GradeController::class);
    });

    // ========================================================================
    // ALUMNI ROUTES
    // ========================================================================

    Route::middleware('role:alumni,admin')->prefix('alumni')->name('alumni.')->group(function () {
        // Test endpoint untuk alumni
        Route::get('/test', function () {
            return response()->json([
                'message' => 'Selamat datang Alumni!',
                'user' => request()->user()->only(['id', 'name', 'email', 'role', 'alumni'])
            ]);
        })->name('test');

        // Tambahkan route alumni lainnya di sini
        // Route::get('/profile', [AlumniController::class, 'profile']);
        // Route::put('/profile', [AlumniController::class, 'updateProfile']);
    });
});
