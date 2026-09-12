<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Command\CreateGroup;

final readonly class CreateGroupCommand
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $role = null,
    ) {
    }
}
