<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Exception;

use SWH\UserProfile\Domain\User\ValueObject\Email;

final class UserAlreadyExistsException extends UserDomainException
{
    public static function forEmail(Email $email): self
    {
        return new self(\sprintf('User with email "%s" already exists.', $email->toString()));
    }
}
