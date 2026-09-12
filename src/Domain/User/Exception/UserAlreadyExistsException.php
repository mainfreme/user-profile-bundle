<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\Exception;

final class UserAlreadyExistsException extends UserDomainException
{
    public static function forEmail(string $email): self
    {
        return new self(\sprintf('User with email "%s" already exists.', $email));
    }
}
