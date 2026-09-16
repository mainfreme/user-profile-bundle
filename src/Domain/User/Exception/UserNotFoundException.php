<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Exception;

use SWH\UserProfile\Domain\User\ValueObject\UserId;

final class UserNotFoundException extends UserDomainException
{
    public static function forId(UserId $userId): self
    {
        return new self(\sprintf('User not found for id "%s".', $userId->toString()));
    }
}
