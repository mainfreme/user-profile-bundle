<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\Group\Exception;

use SWH\UserProfile\Domain\User\Exception\UserDomainException;

final class InvalidGroupIdException extends UserDomainException
{
    public static function empty(): self
    {
        return new self('Group id cannot be empty.');
    }

    public static function invalidFormat(string $value): self
    {
        return new self(\sprintf('Group id "%s" has an invalid format.', $value));
    }
}
