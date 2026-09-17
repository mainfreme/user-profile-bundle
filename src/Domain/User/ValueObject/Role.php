<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\ValueObject;

use SWH\UserProfile\Domain\User\Exception\InvalidRoleException;

final readonly class Role
{
    private function __construct(
        private string $value,
    ) {
    }

    /**
     * Defines a new role code (format only — not checked against an allow-list).
     */
    public static function fromCode(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if ('' === $normalized) {
            throw InvalidRoleException::empty();
        }

        if (!str_starts_with($normalized, 'ROLE_')) {
            throw InvalidRoleException::invalidPrefix($value);
        }

        return new self($normalized);
    }

    /**
     * @param list<string> $allowedRoles
     */
    public static function fromString(string $value, array $allowedRoles): self
    {
        $role = self::fromCode($value);

        if (!\in_array($role->toString(), $allowedRoles, true)) {
            throw InvalidRoleException::notAllowed($role->toString(), $allowedRoles);
        }

        return $role;
    }

    public static function restore(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * @return list<string>
     */
    public function securityRoles(): array
    {
        $roles = [$this->value];

        if ('ROLE_USER' !== $this->value) {
            $roles[] = 'ROLE_USER';
        }

        return array_values(array_unique($roles));
    }
}
