<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\ValueObject;

use SWH\UserProfile\Domain\User\Exception\InvalidBioException;

final readonly class Bio
{
    private function __construct(
        private string $value,
    ) {
    }

    public static function empty(): self
    {
        return new self('');
    }

    public static function fromString(string $value, int $maxLength): self
    {
        $trimmed = trim($value);

        if (mb_strlen($trimmed) > $maxLength) {
            throw InvalidBioException::tooLong($maxLength);
        }

        return new self($trimmed);
    }

    public static function fromOptionalString(?string $value, int $maxLength): ?self
    {
        if (null === $value || '' === trim($value)) {
            return null;
        }

        return self::fromString($value, $maxLength);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return '' === $this->value;
    }
}
