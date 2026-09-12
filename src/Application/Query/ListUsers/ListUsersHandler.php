<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Query\ListUsers;

use Mainfreme\UserProfile\Application\DTO\UserListResponse;
use Mainfreme\UserProfile\Domain\User\Exception\UserDomainException;
use Mainfreme\UserProfile\Domain\User\Port\UserRepositoryInterface;
use Throwable;

final class ListUsersHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(ListUsersQuery $query): UserListResponse
    {
        try {
            $role = null !== $query->role && '' !== trim($query->role)
                ? strtoupper(trim($query->role))
                : null;

            return UserListResponse::success($this->userRepository->findAll($role));
        } catch (UserDomainException $exception) {
            return UserListResponse::error($exception->getMessage());
        } catch (Throwable) {
            return UserListResponse::error('An unexpected error occurred while listing users.');
        }
    }
}
