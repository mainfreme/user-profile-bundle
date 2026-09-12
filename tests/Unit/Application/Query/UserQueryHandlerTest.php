<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Tests\Unit\Application\Query;

use Mainfreme\UserProfile\Application\Query\GetUser\GetUserHandler;
use Mainfreme\UserProfile\Application\Query\GetUser\GetUserQuery;
use Mainfreme\UserProfile\Application\Query\ListUsers\ListUsersHandler;
use Mainfreme\UserProfile\Application\Query\ListUsers\ListUsersQuery;
use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;
use Mainfreme\UserProfile\Domain\User\Model\User;
use Mainfreme\UserProfile\Domain\User\ValueObject\Bio;
use Mainfreme\UserProfile\Domain\User\ValueObject\DisplayName;
use Mainfreme\UserProfile\Domain\User\ValueObject\Email;
use Mainfreme\UserProfile\Domain\User\ValueObject\Role;
use Mainfreme\UserProfile\Infrastructure\Persistence\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class UserQueryHandlerTest extends TestCase
{
    public function test_get_user_returns_profile(): void
    {
        $repository = new InMemoryUserRepository();
        $user = $this->createUser('jan@example.com', 'ROLE_USER');
        $repository->save($user);

        $handler = new GetUserHandler($repository);
        $response = $handler(new GetUserQuery($user->id()->toString()));

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertNotNull($response->user);
        self::assertSame('jan@example.com', $response->user->email);
    }

    public function test_list_users_filters_by_role(): void
    {
        $repository = new InMemoryUserRepository();
        $repository->save($this->createUser('jan@example.com', 'ROLE_USER'));
        $repository->save($this->createUser('anna@example.com', 'ROLE_ADMIN'));

        $handler = new ListUsersHandler($repository);
        $response = $handler(new ListUsersQuery('ROLE_ADMIN'));

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertCount(1, $response->users);
        self::assertSame('anna@example.com', $response->users[0]->email);
    }

    private function createUser(string $email, string $role): User
    {
        return User::register(
            email: Email::fromString($email),
            displayName: DisplayName::fromString('Użytkownik'),
            role: Role::fromString($role, ['ROLE_USER', 'ROLE_ADMIN']),
            bio: Bio::fromString('Bio', 2000),
        );
    }
}
