<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Query\ListUsers;

use SWH\UserProfile\Domain\User\Enum\UserRole;

final readonly class ListUsersQuery
{
    public function __construct(
        public ?UserRole $role = null,
    ) {
    }
}
