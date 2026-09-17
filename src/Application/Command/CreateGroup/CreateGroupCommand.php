<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\CreateGroup;

use SWH\UserProfile\Domain\Group\ValueObject\GroupDescription;
use SWH\UserProfile\Domain\Group\ValueObject\GroupName;
use SWH\UserProfile\Domain\User\Enum\UserRole;

final readonly class CreateGroupCommand
{
    public function __construct(
        public GroupName $name,
        public ?GroupDescription $description = null,
        public ?UserRole $role = null,
    ) {
    }
}
