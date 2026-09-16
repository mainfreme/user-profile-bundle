<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Application\Query;

use SWH\UserProfile\Application\Query\GetRoleTree\GetRoleTreeHandler;
use SWH\UserProfile\Application\Query\GetRoleTree\GetRoleTreeQuery;
use SWH\UserProfile\Domain\User\Enum\OperationStatus;
use SWH\UserProfile\Domain\User\Model\RoleTree;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryRoleCatalog;
use PHPUnit\Framework\TestCase;

final class GetRoleTreeHandlerTest extends TestCase
{
    public function test_returns_role_hierarchy(): void
    {
        $handler = new GetRoleTreeHandler(new InMemoryRoleCatalog(RoleTree::defaultConfig()));
        $response = $handler(new GetRoleTreeQuery());

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertNotEmpty($response->roles);
        self::assertSame('ROLE_ADMIN', $response->roles[0]['role']);

        $moderator = $response->roles[0]['children'][0] ?? null;
        self::assertIsArray($moderator);
        self::assertSame('ROLE_MODERATOR', $moderator['role']);

        $user = $moderator['children'][0] ?? null;
        self::assertIsArray($user);
        self::assertSame('ROLE_USER', $user['role']);
    }
}
