<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Exception;

final class InvalidRoleException extends UserDomainException
{
    public static function empty(): self
    {
        return new self('Role cannot be empty.');
    }

    public static function invalidPrefix(string $role): self
    {
        return new self(\sprintf('Role "%s" is invalid. Role must start with "ROLE_".', $role));
    }

    /**
     * @param list<string> $allowedRoles
     */
    public static function notAllowed(string $role, array $allowedRoles): self
    {
        return new self(\sprintf(
            'Role "%s" is not allowed. Allowed roles: %s.',
            $role,
            implode(', ', $allowedRoles),
        ));
    }

    public static function alreadyExists(string $role): self
    {
        return new self(\sprintf('Role "%s" already exists.', $role));
    }

    public static function parentNotFound(string $parentRole): self
    {
        return new self(\sprintf('Parent role "%s" was not found.', $parentRole));
    }

    public static function invalidLabel(): self
    {
        return new self('Role label cannot be empty.');
    }
}
