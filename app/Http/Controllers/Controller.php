<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API Buku Induk Digital',
    version: '1.0.0',
    description: 'REST API untuk sistem Buku Induk Digital',
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
    description: 'Local development server'
)]
#[OA\Server(
    url: 'https://api.example.com',
    description: 'Production server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
abstract class Controller
{
    //
}
