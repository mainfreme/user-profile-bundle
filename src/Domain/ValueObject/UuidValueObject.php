<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\ValueObject;

use SWH\UserProfile\Domain\User\Exception\UserDomainException;

abstract readonly class UuidValueObject
{
    private const PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    final protected function __construct(
        private string $value,
    ) {
    }

    public static function generate(): static
    {
        $bytes = random_bytes(16);
        $bytes[6] = \chr((\ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = \chr((\ord($bytes[8]) & 0x3F) | 0x80);
        $hex = bin2hex($bytes);

        return new static(\sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        ));
    }

    public static function fromString(string $value): static
    {
        $trimmed = trim($value);

        if ('' === $trimmed) {
            throw static::emptyException();
        }

        if (!preg_match(self::PATTERN, $trimmed)) {
            throw static::invalidFormatException($value);
        }

        return new static(strtolower($trimmed));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $other instanceof static && $this->value === $other->value;
    }

    abstract protected static function emptyException(): UserDomainException;

    abstract protected static function invalidFormatException(string $value): UserDomainException;
}
