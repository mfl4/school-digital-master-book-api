<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ============================================================================
// PUBLIC ROUTES - Tidak memerlukan autentikasi
// ============================================================================

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');

// ============================================================================
// PROTECTED ROUTES - Memerlukan token autentikasi
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {

    // --- Authentication Routes ---
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    Route::get('/current-user', [AuthController::class, 'currentUser'])
        ->name('auth.current-user');

    // Hanya user dengan role 'admin' yang bisa mengakses
    Route::middleware('role:admin')->name('admin.')->group(function () {});

    // Hanya user dengan role 'admin' dan 'guru' yang bisa mengakses
    Route::middleware(['role:admin', 'role:guru'])->name('guru.')->group(function () {});

    // Hanya user dengan role 'admin' dan 'wali_kelas' yang bisa mengakses
    Route::middleware(['role:admin', 'role:wali_kelas'])->name('wali_kelas.')->group(function () {});

    // Hanya user dengan role 'admin' dan 'alumni' yang bisa mengakses
    Route::middleware(['role:admin', 'role:alumni'])->name('alumni.')->group(function () {});
});
