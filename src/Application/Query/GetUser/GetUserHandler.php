<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Query\GetUser;

use SWH\UserProfile\Application\DTO\UserResponse;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Exception\UserNotFoundException;
use SWH\UserProfile\Domain\User\Port\UserRepositoryInterface;
use Throwable;

final class GetUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(GetUserQuery $query): UserResponse
    {
        try {
            $user = $this->userRepository->findById($query->userId);

            if (null === $user) {
                throw UserNotFoundException::forId($query->userId);
            }

            return UserResponse::success($user);
        } catch (UserDomainException $exception) {
            return UserResponse::fromException($exception);
        } catch (Throwable) {
            return UserResponse::error('An unexpected error occurred while reading the user.');
        }
    }
}
