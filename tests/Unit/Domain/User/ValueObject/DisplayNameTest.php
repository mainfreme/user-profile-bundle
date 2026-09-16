<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Domain\User\ValueObject;

use SWH\UserProfile\Domain\User\Exception\InvalidDisplayNameException;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use PHPUnit\Framework\TestCase;

final class DisplayNameTest extends TestCase
{
    public function test_creates_valid_display_name(): void
    {
        $name = DisplayName::fromString('  Jan Kowalski  ');

        self::assertSame('Jan Kowalski', $name->toString());
    }

    public function test_rejects_empty_display_name(): void
    {
        $this->expectException(InvalidDisplayNameException::class);

        DisplayName::fromString(' ');
    }

    public function test_rejects_too_short_display_name(): void
    {
        $this->expectException(InvalidDisplayNameException::class);

        DisplayName::fromString('J');
    }
}
