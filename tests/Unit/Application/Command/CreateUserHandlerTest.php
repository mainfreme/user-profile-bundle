<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Tests\Unit\Application\Command;

use Mainfreme\UserProfile\Application\Command\CreateUser\CreateUserCommand;
use Mainfreme\UserProfile\Application\Command\CreateUser\CreateUserHandler;
use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;
use Mainfreme\UserProfile\Domain\User\Model\RoleTree;
use Mainfreme\UserProfile\Domain\User\Port\PasswordHasherInterface;
use Mainfreme\UserProfile\Infrastructure\Persistence\InMemoryRoleCatalog;
use Mainfreme\UserProfile\Infrastructure\Persistence\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class CreateUserHandlerTest extends TestCase
{
    public function test_creates_user_with_default_role_and_bio(): void
    {
        $handler = $this->createHandler();
        $response = $handler(new CreateUserCommand(
            email: 'jan@example.com',
            displayName: 'Jan Kowalski',
            bio: 'Lubię Symfony',
        ));

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertNotNull($response->user);
        self::assertSame('ROLE_USER', $response->user->role);
        self::assertSame('Lubię Symfony', $response->user->bio);
    }

    public function test_rejects_duplicate_email(): void
    {
        $handler = $this->createHandler();
        $handler(new CreateUserCommand('jan@example.com', 'Jan Kowalski'));
        $response = $handler(new CreateUserCommand('jan@example.com', 'Inny Jan'));

        self::assertSame(OperationStatus::Error, $response->status);
        self::assertStringContainsString('already exists', $response->errorMessage ?? '');
    }

    private function createHandler(): CreateUserHandler
    {
        $hasher = $this->createMock(PasswordHasherInterface::class);
        $hasher->method('hash')->willReturn('hashed');

        return new CreateUserHandler(
            new InMemoryUserRepository(),
            $hasher,
            new InMemoryRoleCatalog(RoleTree::fromFlatList(['ROLE_USER', 'ROLE_ADMIN'])),
            'ROLE_USER',
            2000,
        );
    }
}
