<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Application\Query;

use SWH\UserProfile\Application\Query\GetUser\GetUserHandler;
use SWH\UserProfile\Application\Query\GetUser\GetUserQuery;
use SWH\UserProfile\Application\Query\ListUsers\ListUsersHandler;
use SWH\UserProfile\Application\Query\ListUsers\ListUsersQuery;
use SWH\UserProfile\Domain\User\Enum\OperationStatus;
use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\Model\User;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class UserQueryHandlerTest extends TestCase
{
    public function test_get_user_returns_profile(): void
    {
        $repository = new InMemoryUserRepository();
        $user = $this->createUser('jan@example.com', 'ROLE_USER');
        $repository->save($user);

        $handler = new GetUserHandler($repository);
        $response = $handler(new GetUserQuery($user->id()));

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
        $response = $handler(new ListUsersQuery(UserRole::Admin));

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertCount(1, $response->users);
        self::assertSame('anna@example.com', $response->users[0]->email);
    }

    private function createUser(string $email, string $role): User
    {
        return User::register(
            email: Email::fromString($email),
            displayName: DisplayName::fromString('Użytkownik'),
            role: UserRole::fromString($role),
            bio: Bio::fromString('Bio', 2000),
        );
    }
}
