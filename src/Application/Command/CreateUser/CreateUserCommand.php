<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\CreateUser;

use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use SWH\UserProfile\Domain\User\ValueObject\PlainPassword;

final readonly class CreateUserCommand
{
    public function __construct(
        public Email $email,
        public DisplayName $displayName,
        public ?Bio $bio = null,
        public ?UserRole $role = null,
        public ?PlainPassword $password = null,
    ) {
    }
}
