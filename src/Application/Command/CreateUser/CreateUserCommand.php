<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Command\CreateUser;

final readonly class CreateUserCommand
{
    public function __construct(
        public string $email,
        public string $displayName,
        public ?string $bio = null,
        public ?string $role = null,
        public ?string $password = null,
    ) {
    }
}
