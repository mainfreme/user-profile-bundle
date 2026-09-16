<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Domain\User\ValueObject;

use SWH\UserProfile\Domain\User\Exception\InvalidPasswordException;
use SWH\UserProfile\Domain\User\ValueObject\PlainPassword;
use PHPUnit\Framework\TestCase;

final class PlainPasswordTest extends TestCase
{
    public function test_creates_password(): void
    {
        $password = PlainPassword::fromString('secret123');

        self::assertSame('secret123', $password->toString());
    }

    public function test_from_optional_string_returns_null_for_empty(): void
    {
        self::assertNull(PlainPassword::fromOptionalString(null));
        self::assertNull(PlainPassword::fromOptionalString(''));
    }

    public function test_rejects_too_short_password(): void
    {
        $this->expectException(InvalidPasswordException::class);

        PlainPassword::fromString('short');
    }
}
