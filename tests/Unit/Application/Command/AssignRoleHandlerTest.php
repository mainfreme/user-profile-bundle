<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Application\Command;

use SWH\UserProfile\Application\Command\AssignRole\AssignRoleCommand;
use SWH\UserProfile\Application\Command\AssignRole\AssignRoleHandler;
use SWH\UserProfile\Domain\User\Enum\OperationStatus;
use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\Model\RoleTree;
use SWH\UserProfile\Domain\User\Model\User;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryRoleCatalog;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class AssignRoleHandlerTest extends TestCase
{
    public function test_assigns_allowed_role(): void
    {
        $repository = new InMemoryUserRepository();
        $user = $this->createUser();
        $repository->save($user);

        $handler = new AssignRoleHandler($repository, new InMemoryRoleCatalog(RoleTree::fromFlatList(['ROLE_USER', 'ROLE_ADMIN'])));
        $response = $handler(new AssignRoleCommand($user->id(), UserRole::Admin));

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
        $response = $handler(new AssignRoleCommand($user->id(), UserRole::Moderator));

        self::assertSame(OperationStatus::Error, $response->status);
        self::assertStringContainsString('not allowed', $response->errorMessage ?? '');
    }

    private function createUser(): User
    {
        return User::register(
            email: Email::fromString('jan@example.com'),
            displayName: DisplayName::fromString('Jan Kowalski'),
            role: UserRole::User,
            bio: Bio::empty(),
        );
    }
}
