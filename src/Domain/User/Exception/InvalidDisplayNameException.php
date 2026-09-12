<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\Exception;

final class InvalidDisplayNameException extends UserDomainException
{
    public static function empty(): self
    {
        return new self('Display name cannot be empty.');
    }

    public static function invalidLength(int $min, int $max): self
    {
        return new self(\sprintf('Display name must be between %d and %d characters.', $min, $max));
    }
}
