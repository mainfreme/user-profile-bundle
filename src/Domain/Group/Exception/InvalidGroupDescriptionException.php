<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\Group\Exception;

use SWH\UserProfile\Domain\User\Exception\UserDomainException;

final class InvalidGroupDescriptionException extends UserDomainException
{
    public static function tooLong(int $maxLength): self
    {
        return new self(\sprintf('Group description exceeds maximum allowed length of %d characters.', $maxLength));
    }
}
