<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\ValueObject;

use Mainfreme\UserProfile\Domain\User\Exception\InvalidUserIdException;

final readonly class UserId
{
    private const PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    private function __construct(
        private string $value,
    ) {
    }

    public static function generate(): self
    {
        $bytes = random_bytes(16);
        $bytes[6] = \chr((\ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = \chr((\ord($bytes[8]) & 0x3F) | 0x80);
        $hex = bin2hex($bytes);

        return new self(\sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        ));
    }

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if ('' === $trimmed) {
            throw InvalidUserIdException::empty();
        }

        if (!preg_match(self::PATTERN, $trimmed)) {
            throw InvalidUserIdException::invalidFormat($value);
        }

        return new self(strtolower($trimmed));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
