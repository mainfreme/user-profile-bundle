<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Command\CreateUser;

use Mainfreme\UserProfile\Application\DTO\UserResponse;
use Mainfreme\UserProfile\Domain\User\Exception\UserAlreadyExistsException;
use Mainfreme\UserProfile\Domain\User\Exception\UserDomainException;
use Mainfreme\UserProfile\Domain\User\Model\User;
use Mainfreme\UserProfile\Domain\User\Port\PasswordHasherInterface;
use Mainfreme\UserProfile\Domain\User\Port\RoleCatalogInterface;
use Mainfreme\UserProfile\Domain\User\Port\UserRepositoryInterface;
use Mainfreme\UserProfile\Domain\User\ValueObject\Bio;
use Mainfreme\UserProfile\Domain\User\ValueObject\DisplayName;
use Mainfreme\UserProfile\Domain\User\ValueObject\Email;
use Mainfreme\UserProfile\Domain\User\ValueObject\Role;
use Throwable;

final class CreateUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly RoleCatalogInterface $roleCatalog,
        private readonly string $defaultRole,
        private readonly int $bioMaxLength,
    ) {
    }

    public function __invoke(CreateUserCommand $command): UserResponse
    {
        try {
            $email = Email::fromString($command->email);
            $displayName = DisplayName::fromString($command->displayName);
            $role = Role::fromString($command->role ?? $this->defaultRole, $this->roleCatalog->tree()->flattenCodes());
            $bio = Bio::fromString($command->bio ?? '', $this->bioMaxLength);

            if (null !== $this->userRepository->findByEmail($email)) {
                throw UserAlreadyExistsException::forEmail($email->toString());
            }

            User::assertPlainPassword($command->password);

            $passwordHash = null;
            if (null !== $command->password && '' !== $command->password) {
                $passwordHash = $this->passwordHasher->hash($command->password);
            }

            $user = User::register(
                email: $email,
                displayName: $displayName,
                role: $role,
                bio: $bio,
                passwordHash: $passwordHash,
            );

            $this->userRepository->save($user);

            return UserResponse::success($user);
        } catch (UserDomainException $exception) {
            return UserResponse::error($exception->getMessage());
        } catch (Throwable) {
            return UserResponse::error('An unexpected error occurred while creating the user.');
        }
    }
}
