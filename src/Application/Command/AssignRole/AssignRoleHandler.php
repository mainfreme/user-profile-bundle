<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Command\AssignRole;

use Mainfreme\UserProfile\Application\DTO\UserResponse;
use Mainfreme\UserProfile\Domain\User\Exception\UserDomainException;
use Mainfreme\UserProfile\Domain\User\Exception\UserNotFoundException;
use Mainfreme\UserProfile\Domain\User\Port\RoleCatalogInterface;
use Mainfreme\UserProfile\Domain\User\Port\UserRepositoryInterface;
use Mainfreme\UserProfile\Domain\User\ValueObject\Role;
use Mainfreme\UserProfile\Domain\User\ValueObject\UserId;
use Throwable;

final class AssignRoleHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly RoleCatalogInterface $roleCatalog,
    ) {
    }

    public function __invoke(AssignRoleCommand $command): UserResponse
    {
        try {
            $userId = UserId::fromString($command->userId);
            $user = $this->userRepository->findById($userId);

            if (null === $user) {
                throw UserNotFoundException::forId($userId->toString());
            }

            $role = Role::fromString($command->role, $this->roleCatalog->tree()->flattenCodes());
            $user->assignRole($role);
            $this->userRepository->save($user);

            return UserResponse::success($user);
        } catch (UserDomainException $exception) {
            return UserResponse::error($exception->getMessage());
        } catch (Throwable) {
            return UserResponse::error('An unexpected error occurred while assigning the role.');
        }
    }
}
