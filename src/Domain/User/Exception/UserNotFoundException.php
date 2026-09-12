<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\Exception;

final class UserNotFoundException extends UserDomainException
{
    public static function forId(string $userId): self
    {
        return new self(\sprintf('User not found for id "%s".', $userId));
    }
}
