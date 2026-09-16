<?php

declare(strict_types=1);

namespace SWH\UserProfile\Infrastructure\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'User Profile API',
    version: '1.0.0',
    description: 'REST API do edycji profilu użytkownika (Bio) oraz przypisanej roli',
)]
#[OA\Server(url: '/', description: 'Aplikacja hostująca bundle')]
final class OpenApiSpec
{
}
