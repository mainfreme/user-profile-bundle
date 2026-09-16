<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Query\ListUsers;

use SWH\UserProfile\Application\DTO\UserListResponse;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Port\UserRepositoryInterface;
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
            return UserListResponse::success($this->userRepository->findAll($query->role?->value));
        } catch (UserDomainException $exception) {
            return UserListResponse::fromException($exception);
        } catch (Throwable) {
            return UserListResponse::error('An unexpected error occurred while listing users.');
        }
    }
}
