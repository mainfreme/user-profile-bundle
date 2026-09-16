<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Enum;

use SWH\UserProfile\Domain\User\Exception\InvalidRoleException;

enum UserRole: string
{
    case User = 'ROLE_USER';
    case Moderator = 'ROLE_MODERATOR';
    case Admin = 'ROLE_ADMIN';

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));
        $role = self::tryFrom($normalized);

        if (null === $role) {
            throw InvalidRoleException::notAllowed($normalized, self::values());
        }

        return $role;
    }

    public static function tryFromString(?string $value): ?self
    {
        if (null === $value || '' === trim($value)) {
            return null;
        }

        return self::fromString($value);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $role): string => $role->value, self::cases());
    }

    public function toString(): string
    {
        return $this->value;
    }
}
