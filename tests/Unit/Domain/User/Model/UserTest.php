<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Domain\User\Model;

use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\Model\User;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function test_register_creates_user_with_bio_and_role(): void
    {
        $user = $this->createUser();

        self::assertSame('jan@example.com', $user->email()->toString());
        self::assertSame('Lubię PHP', $user->bio()->toString());
        self::assertSame('ROLE_USER', $user->role()->toString());
    }

    public function test_update_profile_changes_bio(): void
    {
        $user = $this->createUser();
        $previousUpdatedAt = $user->updatedAt();

        $user->updateProfile(
            Bio::fromString('Nowe bio', 2000),
            DisplayName::fromString('Jan Nowak'),
        );

        self::assertSame('Nowe bio', $user->bio()->toString());
        self::assertSame('Jan Nowak', $user->displayName()->toString());
        self::assertGreaterThanOrEqual($previousUpdatedAt, $user->updatedAt());
    }

    public function test_assign_role_changes_assigned_role(): void
    {
        $user = $this->createUser();

        $user->assignRole(UserRole::Admin);

        self::assertSame('ROLE_ADMIN', $user->role()->toString());
    }

    private function createUser(): User
    {
        return User::register(
            email: Email::fromString('jan@example.com'),
            displayName: DisplayName::fromString('Jan Kowalski'),
            role: UserRole::User,
            bio: Bio::fromString('Lubię PHP', 2000),
        );
    }
}
