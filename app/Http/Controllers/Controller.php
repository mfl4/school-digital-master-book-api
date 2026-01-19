<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API Buku Induk Digital',
    version: '1.0.0',
    description: 'REST API untuk sistem Buku Induk Digital - Aplikasi manajemen data siswa, alumni, dan raport untuk institusi pendidikan.',
    contact: new OA\Contact(
        name: 'API Support',
        email: 'support@example.com',
        url: 'https://example.com/support'
    ),
    license: new OA\License(
        name: 'Apache 2.0',
        url: 'https://www.apache.org/licenses/LICENSE-2.0.html'
    )
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Local Development Server'
)]
#[OA\Server(
    url: 'https://api.example.com',
    description: 'Production Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Masukkan token yang didapat dari endpoint login. Format: Bearer {token}'
)]
#[OA\Tag(
    name: 'Authentication',
    description: 'Endpoint untuk autentikasi pengguna (login, logout, refresh token)'
)]
#[OA\Tag(
    name: 'Admin',
    description: 'Endpoint khusus untuk admin'
)]
#[OA\Tag(
    name: 'Teacher',
    description: 'Endpoint untuk guru dan wali kelas'
)]
abstract class Controller
{
    //
}
