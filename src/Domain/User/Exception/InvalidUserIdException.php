<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\Exception;

final class InvalidUserIdException extends UserDomainException
{
    public static function empty(): self
    {
        return new self('User ID cannot be empty.');
    }

    public static function invalidFormat(string $userId): self
    {
        return new self(\sprintf('User ID "%s" has an invalid format.', $userId));
    }
}
