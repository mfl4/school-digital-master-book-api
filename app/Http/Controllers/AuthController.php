<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Login user dan generate token Sanctum
     */
    #[OA\Post(
        path: '/api/login',
        tags: ['Authentication'],
        summary: 'Login pengguna',
        description: 'Autentikasi pengguna dengan email dan password, mengembalikan access token untuk API',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'admin@mail.com',
                        description: 'Email pengguna terdaftar'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'password',
                        minLength: 6,
                        description: 'Password minimal 6 karakter'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Login berhasil'),
                        new OA\Property(property: 'access_token', type: 'string', example: '1|abc123xyz...'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                        new OA\Property(
                            property: 'user',
                            ref: '#/components/schemas/User'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Kredensial tidak valid',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Email atau password salah')
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validasi gagal',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Data yang diberikan tidak valid'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: ['email' => ['Email wajib diisi']]
                        )
                    ]
                )
            )
        ]
    )]
    public function login(Request $request): JsonResponse
    {
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

        // Validasi kredensial
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Hapus token lama (opsional - untuk single device login)
        // $user->tokens()->delete();

        // Buat token baru dengan nama yang deskriptif
        $tokenName = 'auth_token_' . $user->id . '_' . now()->timestamp;
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
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
     */
    #[OA\Post(
        path: '/api/logout',
        tags: ['Authentication'],
        summary: 'Logout pengguna',
        description: 'Menghapus access token pengguna yang sedang aktif',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Logout berhasil')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Tidak terautentikasi',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            )
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Logout dari semua perangkat
     */
    #[OA\Post(
        path: '/api/logout-all',
        tags: ['Authentication'],
        summary: 'Logout dari semua perangkat',
        description: 'Menghapus semua access token pengguna (logout dari semua device)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout dari semua perangkat berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Logout dari semua perangkat berhasil')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Tidak terautentikasi',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            )
        ]
    )]
    public function logoutAll(Request $request): JsonResponse
    {
        // Hapus semua token user
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout dari semua perangkat berhasil'
        ]);
    }

    /**
     * Get data user yang sedang login
     */
    #[OA\Get(
        path: '/api/current-user',
        tags: ['Authentication'],
        summary: 'Data pengguna saat ini',
        description: 'Mendapatkan data lengkap pengguna yang sedang terautentikasi',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data user berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Data user berhasil diambil'),
                        new OA\Property(
                            property: 'user',
                            ref: '#/components/schemas/User'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Tidak terautentikasi',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            )
        ]
    )]
    public function currentUser(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Data user berhasil diambil',
            'user' => $request->user()
        ]);
    }

    /**
     * Refresh token - buat token baru dan hapus yang lama
     */
    #[OA\Post(
        path: '/api/refresh-token',
        tags: ['Authentication'],
        summary: 'Refresh access token',
        description: 'Membuat access token baru dan menghapus token yang sedang digunakan',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token berhasil di-refresh',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Token berhasil di-refresh'),
                        new OA\Property(property: 'access_token', type: 'string', example: '2|xyz789abc...'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Tidak terautentikasi',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            )
        ]
    )]
    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();

        // Hapus token saat ini
        $user->currentAccessToken()->delete();

        // Buat token baru
        $tokenName = 'auth_token_' . $user->id . '_' . now()->timestamp;
        $newToken = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'message' => 'Token berhasil di-refresh',
            'access_token' => $newToken,
            'token_type' => 'Bearer'
        ]);
    }
}
