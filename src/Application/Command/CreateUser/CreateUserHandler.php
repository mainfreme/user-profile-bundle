<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\CreateUser;

use SWH\UserProfile\Application\DTO\UserResponse;
use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\Exception\UserAlreadyExistsException;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Model\User;
use SWH\UserProfile\Domain\User\Port\PasswordHasherInterface;
use SWH\UserProfile\Domain\User\Port\UserRepositoryInterface;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use Throwable;

final class CreateUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly string $defaultRole,
        private readonly int $bioMaxLength,
    ) {
    }

    public function __invoke(CreateUserCommand $command): UserResponse
    {
        try {
            if (null !== $this->userRepository->findByEmail($command->email)) {
                throw UserAlreadyExistsException::forEmail($command->email);
            }

            $passwordHash = null;
            if (null !== $command->password) {
                $passwordHash = $this->passwordHasher->hash($command->password->toString());
            }

            $user = User::register(
                email: $command->email,
                displayName: $command->displayName,
                role: $command->role ?? UserRole::fromString($this->defaultRole),
                bio: Bio::fromString($command->bio?->toString() ?? '', $this->bioMaxLength),
                passwordHash: $passwordHash,
            );

            $this->userRepository->save($user);

            return UserResponse::success($user);
        } catch (UserDomainException $exception) {
            return UserResponse::fromException($exception);
        } catch (Throwable) {
            return UserResponse::error('An unexpected error occurred while creating the user.');
        }
    }
}
