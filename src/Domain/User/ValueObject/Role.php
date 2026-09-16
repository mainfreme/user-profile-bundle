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
     * @param list<string> $allowedRoles
     */
    public static function fromString(string $value, array $allowedRoles): self
    {
        $normalized = strtoupper(trim($value));

        if ('' === $normalized) {
            throw InvalidRoleException::empty();
        }

        if (!str_starts_with($normalized, 'ROLE_')) {
            throw InvalidRoleException::invalidPrefix($value);
        }

        if (!\in_array($normalized, $allowedRoles, true)) {
            throw InvalidRoleException::notAllowed($normalized, $allowedRoles);
        }

        return new self($normalized);
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
