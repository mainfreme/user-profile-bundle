<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\Group\ValueObject;

use SWH\UserProfile\Domain\Group\Exception\InvalidGroupIdException;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\ValueObject\UuidValueObject;

final readonly class GroupId extends UuidValueObject
{
    protected static function emptyException(): UserDomainException
    {
        return InvalidGroupIdException::empty();
    }

    protected static function invalidFormatException(string $value): UserDomainException
    {
        return InvalidGroupIdException::invalidFormat($value);
    }
}
