<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\Group\ValueObject;

use SWH\UserProfile\Domain\Group\Exception\InvalidGroupDescriptionException;

final readonly class GroupDescription
{
    /**
     * Aligned with host VARCHAR(500) free-text fields (e.g. reason);
     * contact/task description columns are TEXT (no hard DB limit).
     */
    public const MAX_LENGTH = 500;

    private function __construct(
        private string $value,
    ) {
    }

    public static function empty(): self
    {
        return new self('');
    }

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if (mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidGroupDescriptionException::tooLong(self::MAX_LENGTH);
        }

        return new self($trimmed);
    }

    public static function fromOptionalString(?string $value): self
    {
        if (null === $value || '' === trim($value)) {
            return self::empty();
        }

        return self::fromString($value);
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
