<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\Group\Exception;

use Mainfreme\UserProfile\Domain\User\Exception\UserDomainException;

final class InvalidGroupNameException extends UserDomainException
{
    public static function empty(): self
    {
        return new self('Group name cannot be empty.');
    }

    public static function invalidLength(int $min, int $max): self
    {
        return new self(\sprintf('Group name must be between %d and %d characters.', $min, $max));
    }
}
