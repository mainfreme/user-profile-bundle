<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\ValueObject;

use Mainfreme\UserProfile\Domain\User\Exception\InvalidEmailException;

final readonly class Email
{
    private function __construct(
        private string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if ('' === $trimmed) {
            throw InvalidEmailException::empty();
        }

        $normalized = strtolower($trimmed);

        if (false === filter_var($normalized, \FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::invalidFormat($value);
        }

        return new self($normalized);
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
