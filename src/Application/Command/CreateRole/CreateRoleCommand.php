<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\CreateRole;

final readonly class CreateRoleCommand
{
    public function __construct(
        public string $role,
        public string $label,
        public ?string $parentRole = null,
    ) {
    }
}
