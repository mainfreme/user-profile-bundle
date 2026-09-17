<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\ValueObject;

use SWH\UserProfile\Domain\User\Exception\InvalidRoleException;

final readonly class RoleLabel
{
    /**
     * Aligned with host DB: contact_email.label / contact_phone.label VARCHAR(64).
     */
    public const MAX_LENGTH = 64;

    private function __construct(
        private string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if ('' === $trimmed) {
            throw InvalidRoleException::invalidLabel();
        }

        if (mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidRoleException::labelTooLong(self::MAX_LENGTH);
        }

        return new self($trimmed);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
