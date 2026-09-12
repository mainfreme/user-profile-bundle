<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Query\GetUser;

use Mainfreme\UserProfile\Application\DTO\UserResponse;
use Mainfreme\UserProfile\Domain\User\Exception\UserDomainException;
use Mainfreme\UserProfile\Domain\User\Exception\UserNotFoundException;
use Mainfreme\UserProfile\Domain\User\Port\UserRepositoryInterface;
use Mainfreme\UserProfile\Domain\User\ValueObject\UserId;
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
            $userId = UserId::fromString($query->userId);
            $user = $this->userRepository->findById($userId);

            if (null === $user) {
                throw UserNotFoundException::forId($userId->toString());
            }

            return UserResponse::success($user);
        } catch (UserDomainException $exception) {
            return UserResponse::error($exception->getMessage());
        } catch (Throwable) {
            return UserResponse::error('An unexpected error occurred while reading the user.');
        }
    }
}
