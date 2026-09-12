<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\Group\Exception;

use Mainfreme\UserProfile\Domain\User\Exception\UserDomainException;

final class GroupAlreadyExistsException extends UserDomainException
{
    public static function forName(string $name): self
    {
        return new self(\sprintf('Group with name "%s" already exists.', $name));
    }
}
