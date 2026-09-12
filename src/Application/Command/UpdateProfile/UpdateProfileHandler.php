<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Command\UpdateProfile;

use Mainfreme\UserProfile\Application\DTO\UserResponse;
use Mainfreme\UserProfile\Domain\User\Exception\UserDomainException;
use Mainfreme\UserProfile\Domain\User\Exception\UserNotFoundException;
use Mainfreme\UserProfile\Domain\User\Port\UserRepositoryInterface;
use Mainfreme\UserProfile\Domain\User\ValueObject\Bio;
use Mainfreme\UserProfile\Domain\User\ValueObject\DisplayName;
use Mainfreme\UserProfile\Domain\User\ValueObject\UserId;
use Throwable;

final class UpdateProfileHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly int $bioMaxLength,
    ) {
    }

    public function __invoke(UpdateProfileCommand $command): UserResponse
    {
        try {
            $userId = UserId::fromString($command->userId);
            $user = $this->userRepository->findById($userId);

            if (null === $user) {
                throw UserNotFoundException::forId($userId->toString());
            }

            $bio = Bio::fromString($command->bio, $this->bioMaxLength);
            $displayName = null !== $command->displayName && '' !== trim($command->displayName)
                ? DisplayName::fromString($command->displayName)
                : $user->displayName();

            $user->updateProfile($bio, $displayName);
            $this->userRepository->save($user);

            return UserResponse::success($user);
        } catch (UserDomainException $exception) {
            return UserResponse::error($exception->getMessage());
        } catch (Throwable) {
            return UserResponse::error('An unexpected error occurred while updating the profile.');
        }
    }
}
