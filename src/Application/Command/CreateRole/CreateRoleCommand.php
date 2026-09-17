<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\CreateRole;

use SWH\UserProfile\Domain\User\ValueObject\Role;
use SWH\UserProfile\Domain\User\ValueObject\RoleLabel;

final readonly class CreateRoleCommand
{
    public function __construct(
        public Role $role,
        public RoleLabel $label,
        public ?Role $parentRole = null,
    ) {
    }
}
