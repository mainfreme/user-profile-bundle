<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Tests\Unit\Domain\User\ValueObject;

use Mainfreme\UserProfile\Domain\User\Exception\InvalidRoleException;
use Mainfreme\UserProfile\Domain\User\ValueObject\Role;
use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function test_creates_allowed_role(): void
    {
        $role = Role::fromString('role_admin', ['ROLE_USER', 'ROLE_ADMIN']);

        self::assertSame('ROLE_ADMIN', $role->toString());
        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $role->securityRoles());
    }

    public function test_rejects_role_without_prefix(): void
    {
        $this->expectException(InvalidRoleException::class);

        Role::fromString('ADMIN', ['ROLE_ADMIN']);
    }

    public function test_rejects_role_not_in_allowed_list(): void
    {
        $this->expectException(InvalidRoleException::class);

        Role::fromString('ROLE_SUPER_ADMIN', ['ROLE_USER', 'ROLE_ADMIN']);
    }
}
