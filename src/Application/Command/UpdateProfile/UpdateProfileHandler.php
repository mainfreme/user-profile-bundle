<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\UpdateProfile;

use SWH\UserProfile\Application\DTO\UserResponse;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Exception\UserNotFoundException;
use SWH\UserProfile\Domain\User\Port\UserRepositoryInterface;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
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
            $user = $this->userRepository->findById($command->userId);

            if (null === $user) {
                throw UserNotFoundException::forId($command->userId);
            }

            $bio = null !== $command->bio
                ? Bio::fromString($command->bio->toString(), $this->bioMaxLength)
                : $user->bio();
            $displayName = $command->displayName ?? $user->displayName();

            $user->updateProfile($bio, $displayName);
            $this->userRepository->save($user);

            return UserResponse::success($user);
        } catch (UserDomainException $exception) {
            return UserResponse::fromException($exception);
        } catch (Throwable) {
            return UserResponse::error('An unexpected error occurred while updating the profile.');
        }
    }
}
