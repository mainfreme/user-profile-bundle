<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Application\Command;

use SWH\UserProfile\Application\Command\CreateUser\CreateUserCommand;
use SWH\UserProfile\Application\Command\CreateUser\CreateUserHandler;
use SWH\UserProfile\Domain\User\Enum\OperationStatus;
use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\Port\PasswordHasherInterface;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use SWH\UserProfile\Domain\User\ValueObject\PlainPassword;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class CreateUserHandlerTest extends TestCase
{
    public function test_creates_user_with_default_role_and_bio(): void
    {
        $handler = $this->createHandler();
        $response = $handler(new CreateUserCommand(
            email: Email::fromString('jan@example.com'),
            displayName: DisplayName::fromString('Jan Kowalski'),
            bio: Bio::fromString('Lubię Symfony', 2000),
        ));

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertNotNull($response->user);
        self::assertSame('ROLE_USER', $response->user->role);
        self::assertSame('Lubię Symfony', $response->user->bio);
    }

    public function test_creates_user_with_role_enum_and_password(): void
    {
        $hasher = $this->createMock(PasswordHasherInterface::class);
        $hasher->expects(self::once())->method('hash')->with('secret123')->willReturn('hashed');

        $handler = $this->createHandler($hasher);
        $response = $handler(new CreateUserCommand(
            email: Email::fromString('admin@example.com'),
            displayName: DisplayName::fromString('Admin'),
            role: UserRole::Admin,
            password: PlainPassword::fromString('secret123'),
        ));

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertNotNull($response->user);
        self::assertSame('ROLE_ADMIN', $response->user->role);
    }

    public function test_rejects_duplicate_email(): void
    {
        $handler = $this->createHandler();
        $handler(new CreateUserCommand(Email::fromString('jan@example.com'), DisplayName::fromString('Jan Kowalski')));
        $response = $handler(new CreateUserCommand(Email::fromString('jan@example.com'), DisplayName::fromString('Inny Jan')));

        self::assertSame(OperationStatus::Error, $response->status);
        self::assertStringContainsString('already exists', $response->errorMessage ?? '');
    }

    private function createHandler(?PasswordHasherInterface $hasher = null): CreateUserHandler
    {
        if (null === $hasher) {
            $hasher = $this->createMock(PasswordHasherInterface::class);
            $hasher->method('hash')->willReturn('hashed');
        }

        return new CreateUserHandler(
            new InMemoryUserRepository(),
            $hasher,
            'ROLE_USER',
            2000,
        );
    }
}
