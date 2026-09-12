<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\Group\ValueObject;

use Mainfreme\UserProfile\Domain\Group\Exception\InvalidGroupNameException;

final readonly class GroupName
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
            throw InvalidGroupNameException::empty();
        }

        $length = mb_strlen($trimmed);

        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw InvalidGroupNameException::invalidLength(self::MIN_LENGTH, self::MAX_LENGTH);
        }

        return new self($trimmed);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function normalized(): string
    {
        return mb_strtolower($this->value);
    }
}
