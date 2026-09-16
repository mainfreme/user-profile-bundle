<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\ValueObject;

use SWH\UserProfile\Domain\User\Exception\InvalidUserIdException;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\ValueObject\UuidValueObject;

final readonly class UserId extends UuidValueObject
{
    protected static function emptyException(): UserDomainException
    {
        return InvalidUserIdException::empty();
    }

    protected static function invalidFormatException(string $value): UserDomainException
    {
        return InvalidUserIdException::invalidFormat($value);
    }
}
