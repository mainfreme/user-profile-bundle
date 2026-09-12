<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\Port;

use Mainfreme\UserProfile\Domain\User\Model\User;
use Mainfreme\UserProfile\Domain\User\ValueObject\Email;
use Mainfreme\UserProfile\Domain\User\ValueObject\UserId;

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
