<?php

use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/current-user', [AuthController::class, 'currentUser']);
    Route::delete('/logout', [AuthController::class, 'logout']);
});

/**
 * @OA\Get(
 *     path="/api/admin-only-test",
 *     tags={"Admin"},
 *     summary="Admin test endpoint",
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 */

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin-only-test', function () {
        return response()->json([
            'message' => 'Hello Admin'
        ]);
    });
});

Route::middleware(['auth:sanctum', 'role:guru,wali_kelas'])->group(function () {
    Route::get('/teacher-area-test', function () {
        return response()->json([
            'message' => 'Hello Teacher'
        ]);
    });
});
