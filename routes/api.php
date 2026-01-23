<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;

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

// --- Pencarian Data Publik (Berdasarkan NIS/NIM/NIA) ---
Route::prefix('public')->name('public.')->group(function () {
    // Pencarian siswa berdasarkan NIS/NISN
    Route::get('/students/search', [StudentController::class, 'publicSearch'])
        ->name('students.search');

    // Pencarian alumni berdasarkan NIM
    Route::get('/alumni/search', [AlumniController::class, 'publicSearch'])
        ->name('alumni.search');

    // Detail siswa publik (data terbatas)
    Route::get('/students/{nis}', [StudentController::class, 'publicShow'])
        ->name('students.show');

    // Detail alumni publik (data terbatas)
    Route::get('/alumni/{nim}', [AlumniController::class, 'publicShow'])
        ->name('alumni.show');
});

// ============================================================================
// PROTECTED ROUTES - Memerlukan token autentikasi
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {

    // --- Authentication Routes ---
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    Route::get('/current-user', [AuthController::class, 'currentUser'])
        ->name('auth.current-user');

    // ========================================================================
    // ADMIN ROUTES - Hanya user dengan role 'admin' yang bisa mengakses
    // ========================================================================
    Route::middleware('role:admin')->name('admin.')->group(function () {
        // Subjects CRUD
        Route::get('subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::post('subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
        Route::patch('subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

        // Users CRUD
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Students CRUD (Full Access)
        Route::get('students', [StudentController::class, 'index'])->name('students.index');
        Route::post('students', [StudentController::class, 'store'])->name('students.store');
        Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::patch('students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

        // Alumni CRUD (Full Access)
        Route::get('alumni', [AlumniController::class, 'index'])->name('alumni.index');
        Route::post('alumni', [AlumniController::class, 'store'])->name('alumni.store');
        Route::get('alumni/{alumnus}', [AlumniController::class, 'show'])->name('alumni.show');
        Route::patch('alumni/{alumnus}', [AlumniController::class, 'update'])->name('alumni.update');
        Route::delete('alumni/{alumnus}', [AlumniController::class, 'destroy'])->name('alumni.destroy');
    });

    // ========================================================================
    // GURU ROUTES - Hanya user dengan role 'guru'
    // ========================================================================
    Route::middleware('role:guru')->name('guru.')->group(function () {
        // Route untuk guru akan ditambahkan di sini (input nilai, dll)
    });

    // ========================================================================
    // WALI KELAS ROUTES - Hanya user dengan role 'wali_kelas'
    // ========================================================================
    Route::middleware('role:wali_kelas')->name('wali_kelas.')->group(function () {
        // Wali kelas bisa akses data siswa di kelasnya
        Route::get('wali/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('wali/students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::patch('wali/students/{student}', [StudentController::class, 'update'])->name('students.update');
    });

    // ========================================================================
    // ALUMNI ROUTES - Hanya user dengan role 'alumni'
    // ========================================================================
    Route::middleware('role:alumni')->name('alumni.')->group(function () {
        // Alumni bisa update data pribadi sendiri
        Route::get('my-profile', [AlumniController::class, 'myProfile'])->name('my-profile');
        Route::patch('my-profile', [AlumniController::class, 'updateMyProfile'])->name('my-profile.update');
    });
});
