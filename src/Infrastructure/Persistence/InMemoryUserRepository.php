<?php

declare(strict_types=1);

namespace SWH\UserProfile\Infrastructure\Persistence;

use SWH\UserProfile\Domain\User\Model\User;
use SWH\UserProfile\Domain\User\Port\UserRepositoryInterface;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use SWH\UserProfile\Domain\User\ValueObject\UserId;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /**
     * @var array<string, User>
     */
    private array $users = [];

    /**
     * @var array<string, string>
     */
    private array $emailIndex = [];

    public function save(User $user): void
    {
        $this->users[$user->id()->toString()] = $user;
        $this->emailIndex[$user->email()->toString()] = $user->id()->toString();
    }

    public function findById(UserId $userId): ?User
    {
        return $this->users[$userId->toString()] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        $userId = $this->emailIndex[$email->toString()] ?? null;

        if (null === $userId) {
            return null;
        }

        return $this->users[$userId] ?? null;
    }

    public function findAll(?string $role = null): array
    {
        $users = array_values($this->users);

        if (null === $role) {
            return $users;
        }

        return array_values(array_filter(
            $users,
            static fn (User $user): bool => $user->role()->toString() === $role,
        ));
    }
}
