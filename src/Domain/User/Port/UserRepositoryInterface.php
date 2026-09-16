<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Port;

use SWH\UserProfile\Domain\User\Model\User;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use SWH\UserProfile\Domain\User\ValueObject\UserId;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(UserId $userId): ?User;

    public function findByEmail(Email $email): ?User;

    /**
     * @return list<User>
     */
    public function findAll(?string $role = null): array;
}
