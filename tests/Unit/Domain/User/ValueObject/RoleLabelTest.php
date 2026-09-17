<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Domain\User\ValueObject;

use SWH\UserProfile\Domain\User\Exception\InvalidRoleException;
use SWH\UserProfile\Domain\User\ValueObject\RoleLabel;
use PHPUnit\Framework\TestCase;

final class RoleLabelTest extends TestCase
{
    public function test_creates_from_valid_string(): void
    {
        $label = RoleLabel::fromString('  Redaktor  ');

        self::assertSame('Redaktor', $label->toString());
    }

    public function test_rejects_empty(): void
    {
        $this->expectException(InvalidRoleException::class);

        RoleLabel::fromString('   ');
    }

    public function test_rejects_too_long(): void
    {
        $this->expectException(InvalidRoleException::class);

        RoleLabel::fromString(str_repeat('a', RoleLabel::MAX_LENGTH + 1));
    }
}
