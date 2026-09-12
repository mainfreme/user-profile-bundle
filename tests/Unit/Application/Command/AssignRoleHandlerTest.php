<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Tests\Unit\Application\Command;

use Mainfreme\UserProfile\Application\Command\AssignRole\AssignRoleCommand;
use Mainfreme\UserProfile\Application\Command\AssignRole\AssignRoleHandler;
use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;
use Mainfreme\UserProfile\Domain\User\Model\RoleTree;
use Mainfreme\UserProfile\Domain\User\Model\User;
use Mainfreme\UserProfile\Domain\User\ValueObject\Bio;
use Mainfreme\UserProfile\Domain\User\ValueObject\DisplayName;
use Mainfreme\UserProfile\Domain\User\ValueObject\Email;
use Mainfreme\UserProfile\Domain\User\ValueObject\Role;
use Mainfreme\UserProfile\Infrastructure\Persistence\InMemoryRoleCatalog;
use Mainfreme\UserProfile\Infrastructure\Persistence\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class AssignRoleHandlerTest extends TestCase
{
    public function test_assigns_allowed_role(): void
    {
        $repository = new InMemoryUserRepository();
        $user = $this->createUser();
        $repository->save($user);

        $handler = new AssignRoleHandler($repository, new InMemoryRoleCatalog(RoleTree::fromFlatList(['ROLE_USER', 'ROLE_ADMIN'])));
        $response = $handler(new AssignRoleCommand($user->id()->toString(), 'ROLE_ADMIN'));

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertNotNull($response->user);
        self::assertSame('ROLE_ADMIN', $response->user->role);
    }

    public function test_rejects_unknown_role(): void
    {
        $repository = new InMemoryUserRepository();
        $user = $this->createUser();
        $repository->save($user);

        $handler = new AssignRoleHandler($repository, new InMemoryRoleCatalog(RoleTree::fromFlatList(['ROLE_USER', 'ROLE_ADMIN'])));
        $response = $handler(new AssignRoleCommand($user->id()->toString(), 'ROLE_SUPER_ADMIN'));

        self::assertSame(OperationStatus::Error, $response->status);
        self::assertStringContainsString('not allowed', $response->errorMessage ?? '');
    }

    private function createUser(): User
    {
        return User::register(
            email: Email::fromString('jan@example.com'),
            displayName: DisplayName::fromString('Jan Kowalski'),
            role: Role::fromString('ROLE_USER', ['ROLE_USER', 'ROLE_ADMIN']),
            bio: Bio::empty(),
        );
    }
}
