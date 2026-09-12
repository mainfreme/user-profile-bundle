<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Query\ListUsers;

final readonly class ListUsersQuery
{
    public function __construct(
        public ?string $role = null,
    ) {
    }
}
