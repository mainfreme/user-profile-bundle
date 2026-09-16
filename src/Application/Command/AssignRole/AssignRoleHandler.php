<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\AssignRole;

use SWH\UserProfile\Application\DTO\UserResponse;
use SWH\UserProfile\Domain\User\Exception\InvalidRoleException;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Exception\UserNotFoundException;
use SWH\UserProfile\Domain\User\Port\RoleCatalogInterface;
use SWH\UserProfile\Domain\User\Port\UserRepositoryInterface;
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
            $user = $this->userRepository->findById($command->userId);

            if (null === $user) {
                throw UserNotFoundException::forId($command->userId);
            }

            $allowedRoles = $this->roleCatalog->tree()->flattenCodes();
            if (!\in_array($command->role->value, $allowedRoles, true)) {
                throw InvalidRoleException::notAllowed($command->role->value, $allowedRoles);
            }

            $user->assignRole($command->role);

            $this->userRepository->save($user);

            return UserResponse::success($user);
        } catch (UserDomainException $exception) {
            return UserResponse::fromException($exception);
        } catch (Throwable) {
            return UserResponse::error('An unexpected error occurred while assigning the role.');
        }
    }
}
