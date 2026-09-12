<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Query\GetUser;

final readonly class GetUserQuery
{
    public function __construct(
        public string $userId,
    ) {
    }
}
