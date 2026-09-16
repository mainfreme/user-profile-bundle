<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\CreateGroup;

use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\Group\ValueObject\GroupName;

final readonly class CreateGroupCommand
{
    public function __construct(
        public GroupName $name,
        public ?string $description = null,
        public ?UserRole $role = null,
    ) {
    }
}
