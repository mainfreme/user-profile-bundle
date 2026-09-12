<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\Exception;

final class InvalidEmailException extends UserDomainException
{
    public static function empty(): self
    {
        return new self('Email cannot be empty.');
    }

    public static function invalidFormat(string $email): self
    {
        return new self(\sprintf('Email "%s" has an invalid format.', $email));
    }
}
