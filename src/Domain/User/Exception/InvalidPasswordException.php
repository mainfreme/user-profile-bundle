<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\Exception;

final class InvalidPasswordException extends UserDomainException
{
    public static function tooShort(int $minLength): self
    {
        return new self(\sprintf('Password must be at least %d characters long.', $minLength));
    }
}
