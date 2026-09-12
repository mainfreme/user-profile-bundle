<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Tests\Unit\Application\Command;

use Mainfreme\UserProfile\Application\Command\UpdateProfile\UpdateProfileCommand;
use Mainfreme\UserProfile\Application\Command\UpdateProfile\UpdateProfileHandler;
use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;
use Mainfreme\UserProfile\Domain\User\Model\User;
use Mainfreme\UserProfile\Domain\User\ValueObject\Bio;
use Mainfreme\UserProfile\Domain\User\ValueObject\DisplayName;
use Mainfreme\UserProfile\Domain\User\ValueObject\Email;
use Mainfreme\UserProfile\Domain\User\ValueObject\Role;
use Mainfreme\UserProfile\Infrastructure\Persistence\InMemoryUserRepository;
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
            userId: $user->id()->toString(),
            bio: 'Nowe bio',
            displayName: 'Jan Nowak',
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
            userId: '550e8400-e29b-41d4-a716-446655440000',
            bio: 'Bio',
        ));

        self::assertSame(OperationStatus::Error, $response->status);
        self::assertStringContainsString('not found', $response->errorMessage ?? '');
    }

    private function createUser(): User
    {
        return User::register(
            email: Email::fromString('jan@example.com'),
            displayName: DisplayName::fromString('Jan Kowalski'),
            role: Role::fromString('ROLE_USER', ['ROLE_USER']),
            bio: Bio::fromString('Stare bio', 2000),
        );
    }
}
