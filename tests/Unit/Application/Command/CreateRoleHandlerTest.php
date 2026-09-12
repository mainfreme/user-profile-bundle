<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Tests\Unit\Application\Command;

use Mainfreme\UserProfile\Application\Command\CreateRole\CreateRoleCommand;
use Mainfreme\UserProfile\Application\Command\CreateRole\CreateRoleHandler;
use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;
use Mainfreme\UserProfile\Domain\User\Model\RoleTree;
use Mainfreme\UserProfile\Infrastructure\Persistence\InMemoryRoleCatalog;
use PHPUnit\Framework\TestCase;

final class CreateRoleHandlerTest extends TestCase
{
    public function test_adds_role_to_tree(): void
    {
        $catalog = new InMemoryRoleCatalog(RoleTree::defaultConfig());
        $handler = new CreateRoleHandler($catalog);
        $response = $handler(new CreateRoleCommand('ROLE_EDITOR', 'Redaktor', 'ROLE_MODERATOR'));

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertContains('ROLE_EDITOR', $catalog->tree()->flattenCodes());
        self::assertSame('Redaktor', $catalog->tree()->labels()['ROLE_EDITOR']);
    }

    public function test_rejects_duplicate_role(): void
    {
        $handler = new CreateRoleHandler(new InMemoryRoleCatalog(RoleTree::defaultConfig()));
        $response = $handler(new CreateRoleCommand('ROLE_USER', 'Użytkownik', 'ROLE_ADMIN'));

        self::assertSame(OperationStatus::Error, $response->status);
        self::assertStringContainsString('already exists', $response->errorMessage ?? '');
    }
}
