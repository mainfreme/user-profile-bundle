<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Application\Command;

use SWH\UserProfile\Application\Command\CreateRole\CreateRoleCommand;
use SWH\UserProfile\Application\Command\CreateRole\CreateRoleHandler;
use SWH\UserProfile\Domain\User\Enum\OperationStatus;
use SWH\UserProfile\Domain\User\Model\RoleTree;
use SWH\UserProfile\Domain\User\ValueObject\Role;
use SWH\UserProfile\Domain\User\ValueObject\RoleLabel;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryRoleCatalog;
use PHPUnit\Framework\TestCase;

final class CreateRoleHandlerTest extends TestCase
{
    public function test_adds_role_to_tree(): void
    {
        $catalog = new InMemoryRoleCatalog(RoleTree::defaultConfig());
        $handler = new CreateRoleHandler($catalog);
        $response = $handler(new CreateRoleCommand(
            Role::fromCode('ROLE_EDITOR'),
            RoleLabel::fromString('Redaktor'),
            Role::fromCode('ROLE_MODERATOR'),
        ));

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertContains('ROLE_EDITOR', $catalog->tree()->flattenCodes());
        self::assertSame('Redaktor', $catalog->tree()->labels()['ROLE_EDITOR']);
    }

    public function test_rejects_duplicate_role(): void
    {
        $handler = new CreateRoleHandler(new InMemoryRoleCatalog(RoleTree::defaultConfig()));
        $response = $handler(new CreateRoleCommand(
            Role::fromCode('ROLE_USER'),
            RoleLabel::fromString('Użytkownik'),
            Role::fromCode('ROLE_ADMIN'),
        ));

        self::assertSame(OperationStatus::Error, $response->status);
        self::assertStringContainsString('already exists', $response->errorMessage ?? '');
    }
}
