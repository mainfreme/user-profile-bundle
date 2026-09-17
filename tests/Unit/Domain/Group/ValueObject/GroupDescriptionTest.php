<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Domain\Group\ValueObject;

use SWH\UserProfile\Domain\Group\Exception\InvalidGroupDescriptionException;
use SWH\UserProfile\Domain\Group\ValueObject\GroupDescription;
use PHPUnit\Framework\TestCase;

final class GroupDescriptionTest extends TestCase
{
    public function test_creates_from_valid_string(): void
    {
        $description = GroupDescription::fromString('  Zespół redakcyjny  ');

        self::assertSame('Zespół redakcyjny', $description->toString());
    }

    public function test_allows_empty(): void
    {
        self::assertTrue(GroupDescription::empty()->isEmpty());
        self::assertTrue(GroupDescription::fromOptionalString(null)->isEmpty());
    }

    public function test_rejects_too_long(): void
    {
        $this->expectException(InvalidGroupDescriptionException::class);

        GroupDescription::fromString(str_repeat('a', GroupDescription::MAX_LENGTH + 1));
    }
}
