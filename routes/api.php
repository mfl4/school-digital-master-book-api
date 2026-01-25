<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeSummaryController;

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
    // List semua siswa (dengan optional filter)
    Route::get('/students', [StudentController::class, 'publicIndex'])
        ->name('students.index');

    // Pencarian siswa berdasarkan NIS/NISN
    Route::get('/students/search', [StudentController::class, 'publicSearch'])
        ->name('students.search');

    // List semua alumni (dengan optional filter)
    Route::get('/alumni', [AlumniController::class, 'publicIndex'])
        ->name('alumni.index');

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

        // Grades CRUD (Admin bisa akses semua grades)
        Route::get('grades', [GradeController::class, 'index'])->name('grades.index');
        Route::post('grades', [GradeController::class, 'store'])->name('grades.store');
        Route::get('grades/{grade}', [GradeController::class, 'show'])->name('grades.show');
        Route::patch('grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
        Route::delete('grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');

        // Grade Summaries (Read-only untuk admin)
        Route::get('grade-summaries', [GradeSummaryController::class, 'index'])->name('grade-summaries.index');
        Route::get('grade-summaries/{student}/{semester}', [GradeSummaryController::class, 'show'])->name('grade-summaries.show');
    });

    // ========================================================================
    // GURU ROUTES - Hanya user dengan role 'guru'
    // ========================================================================
    Route::middleware('role:guru')->name('guru.')->group(function () {
        // Guru hanya bisa CRUD nilai untuk mapel yang diampu
        Route::get('my-grades', [GradeController::class, 'myGrades'])->name('my-grades.index');
        Route::post('my-grades', [GradeController::class, 'storeMyGrade'])->name('my-grades.store');
        Route::patch('my-grades/{grade}', [GradeController::class, 'updateMyGrade'])->name('my-grades.update');
        Route::delete('my-grades/{grade}', [GradeController::class, 'destroyMyGrade'])->name('my-grades.destroy');
    });

    // ========================================================================
    // WALI KELAS ROUTES - Hanya user dengan role 'wali_kelas'
    // ========================================================================
    Route::middleware('role:wali_kelas')->name('wali_kelas.')->group(function () {
        // Wali kelas bisa akses data siswa di kelasnya
        Route::get('wali/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('wali/students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::patch('wali/students/{student}', [StudentController::class, 'update'])->name('students.update');

        // Wali kelas bisa akses semua grades siswa di kelasnya
        Route::get('wali/grades', [GradeController::class, 'classGrades'])->name('grades.index');
        Route::post('wali/grades', [GradeController::class, 'storeClassGrade'])->name('grades.store');

        // Wali kelas bisa lihat summaries siswa di kelasnya
        Route::get('wali/grade-summaries', [GradeSummaryController::class, 'classSummaries'])->name('summaries.index');
        Route::get('wali/grade-summaries/{student}/{semester}', [GradeSummaryController::class, 'show'])->name('summaries.show');
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
