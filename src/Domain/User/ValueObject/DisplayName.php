<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\ValueObject;

use SWH\UserProfile\Domain\User\Exception\InvalidDisplayNameException;

final readonly class DisplayName
{
    private const MIN_LENGTH = 2;
    private const MAX_LENGTH = 100;

    private function __construct(
        private string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if ('' === $trimmed) {
            throw InvalidDisplayNameException::empty();
        }

        $length = mb_strlen($trimmed);

        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw InvalidDisplayNameException::invalidLength(self::MIN_LENGTH, self::MAX_LENGTH);
        }

        return new self($trimmed);
    }

    public static function fromOptionalString(?string $value): ?self
    {
        if (null === $value || '' === trim($value)) {
            return null;
        }

        return self::fromString($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
