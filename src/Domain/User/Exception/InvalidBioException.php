<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\Exception;

final class InvalidBioException extends UserDomainException
{
    public static function tooLong(int $maxLength): self
    {
        return new self(\sprintf('Bio exceeds maximum allowed length of %d characters.', $maxLength));
    }
}
