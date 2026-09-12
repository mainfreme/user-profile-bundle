<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Tests\Unit\Application\Command;

use Mainfreme\UserProfile\Application\Command\CreateGroup\CreateGroupCommand;
use Mainfreme\UserProfile\Application\Command\CreateGroup\CreateGroupHandler;
use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;
use Mainfreme\UserProfile\Domain\User\Model\RoleTree;
use Mainfreme\UserProfile\Infrastructure\Persistence\InMemoryGroupRepository;
use Mainfreme\UserProfile\Infrastructure\Persistence\InMemoryRoleCatalog;
use PHPUnit\Framework\TestCase;

final class CreateGroupHandlerTest extends TestCase
{
    public function test_creates_group_with_role(): void
    {
        $handler = new CreateGroupHandler(
            new InMemoryGroupRepository(),
            new InMemoryRoleCatalog(RoleTree::defaultConfig()),
        );

        $response = $handler(new CreateGroupCommand('Redakcja', 'Zespół redakcyjny', 'ROLE_MODERATOR'));

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

        $handler(new CreateGroupCommand('Redakcja'));
        $response = $handler(new CreateGroupCommand('Redakcja'));

        self::assertSame(OperationStatus::Error, $response->status);
        self::assertStringContainsString('already exists', $response->errorMessage ?? '');
    }
}
