<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Command\AssignRole;

final readonly class AssignRoleCommand
{
    public function __construct(
        public string $userId,
        public string $role,
    ) {
    }
}
