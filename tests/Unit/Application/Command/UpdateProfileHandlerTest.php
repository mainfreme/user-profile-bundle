<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Application\Command;

use SWH\UserProfile\Application\Command\UpdateProfile\UpdateProfileCommand;
use SWH\UserProfile\Application\Command\UpdateProfile\UpdateProfileHandler;
use SWH\UserProfile\Domain\User\Enum\OperationStatus;
use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\Model\User;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use SWH\UserProfile\Domain\User\ValueObject\UserId;
use SWH\UserProfile\Infrastructure\Persistence\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class UpdateProfileHandlerTest extends TestCase
{
    public function test_updates_bio_and_display_name(): void
    {
        $repository = new InMemoryUserRepository();
        $user = $this->createUser();
        $repository->save($user);

        $handler = new UpdateProfileHandler($repository, 2000);
        $response = $handler(new UpdateProfileCommand(
            userId: $user->id(),
            bio: Bio::fromString('Nowe bio', 2000),
            displayName: DisplayName::fromString('Jan Nowak'),
        ));

        self::assertSame(OperationStatus::Success, $response->status);
        self::assertNotNull($response->user);
        self::assertSame('Nowe bio', $response->user->bio);
        self::assertSame('Jan Nowak', $response->user->displayName);
    }

    public function test_returns_error_when_user_missing(): void
    {
        $handler = new UpdateProfileHandler(new InMemoryUserRepository(), 2000);
        $response = $handler(new UpdateProfileCommand(
            userId: UserId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            bio: Bio::fromString('Bio', 2000),
        ));

        self::assertSame(OperationStatus::Error, $response->status);
        self::assertStringContainsString('not found', $response->errorMessage ?? '');
    }

    private function createUser(): User
    {
        return User::register(
            email: Email::fromString('jan@example.com'),
            displayName: DisplayName::fromString('Jan Kowalski'),
            role: UserRole::User,
            bio: Bio::fromString('Stare bio', 2000),
        );
    }
}
