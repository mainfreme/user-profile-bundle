<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\AssignRole;

use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\ValueObject\UserId;

final readonly class AssignRoleCommand
{
    public function __construct(
        public UserId $userId,
        public UserRole $role,
    ) {
    }
}
