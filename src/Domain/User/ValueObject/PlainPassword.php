<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\ValueObject;

use SWH\UserProfile\Domain\User\Exception\InvalidPasswordException;

final readonly class PlainPassword
{
    public const MIN_LENGTH = 8;

    private function __construct(
        private string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        if (mb_strlen($value) < self::MIN_LENGTH) {
            throw InvalidPasswordException::tooShort(self::MIN_LENGTH);
        }

        return new self($value);
    }

    public static function fromOptionalString(?string $value): ?self
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return self::fromString($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
