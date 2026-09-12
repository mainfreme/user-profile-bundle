<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\Group\Exception;

use Mainfreme\UserProfile\Domain\User\Exception\UserDomainException;

final class InvalidGroupDescriptionException extends UserDomainException
{
    public static function tooLong(int $maxLength): self
    {
        return new self(\sprintf('Group description exceeds maximum allowed length of %d characters.', $maxLength));
    }
}
