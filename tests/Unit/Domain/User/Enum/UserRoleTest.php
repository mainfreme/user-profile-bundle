<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Domain\User\Enum;

use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\Exception\InvalidRoleException;
use PHPUnit\Framework\TestCase;

final class UserRoleTest extends TestCase
{
    public function test_from_string_normalizes_value(): void
    {
        self::assertSame(UserRole::Admin, UserRole::fromString('role_admin'));
    }

    public function test_try_from_string_returns_null_for_empty(): void
    {
        self::assertNull(UserRole::tryFromString(null));
        self::assertNull(UserRole::tryFromString('   '));
    }

    public function test_rejects_unknown_role(): void
    {
        $this->expectException(InvalidRoleException::class);

        UserRole::fromString('ROLE_EDITOR');
    }
}
