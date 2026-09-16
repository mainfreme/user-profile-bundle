<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Application\Command;

use SWH\UserProfile\Application\Command\CreateGroup\CreateGroupCommand;
use SWH\UserProfile\Application\Command\CreateGroup\CreateGroupHandler;
use SWH\UserProfile\Domain\Group\ValueObject\GroupName;
use SWH\UserProfile\Domain\User\Enum\OperationStatus;
use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\Model\RoleTree;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryGroupRepository;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryRoleCatalog;
use PHPUnit\Framework\TestCase;

final class CreateGroupHandlerTest extends TestCase
{
    public function test_creates_group_with_role(): void
    {
        $handler = new CreateGroupHandler(
            new InMemoryGroupRepository(),
            new InMemoryRoleCatalog(RoleTree::defaultConfig()),
        );

        $response = $handler(new CreateGroupCommand(
            GroupName::fromString('Redakcja'),
            'Zespół redakcyjny',
            UserRole::Moderator,
        ));

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertNotNull($response->group);
        self::assertSame('Redakcja', $response->group->name);
        self::assertSame('ROLE_MODERATOR', $response->group->role);
    }

    public function test_rejects_duplicate_group_name(): void
    {
        $handler = new CreateGroupHandler(
            new InMemoryGroupRepository(),
            new InMemoryRoleCatalog(RoleTree::defaultConfig()),
        );

        $handler(new CreateGroupCommand(GroupName::fromString('Redakcja')));
        $response = $handler(new CreateGroupCommand(GroupName::fromString('Redakcja')));

        self::assertSame(OperationStatus::Error, $response->status);
        self::assertStringContainsString('already exists', $response->errorMessage ?? '');
    }
}
