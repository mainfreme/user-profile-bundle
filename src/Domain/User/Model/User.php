<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\Model;

use DateTimeImmutable;
use Mainfreme\UserProfile\Domain\User\Exception\InvalidPasswordException;
use Mainfreme\UserProfile\Domain\User\ValueObject\Bio;
use Mainfreme\UserProfile\Domain\User\ValueObject\DisplayName;
use Mainfreme\UserProfile\Domain\User\ValueObject\Email;
use Mainfreme\UserProfile\Domain\User\ValueObject\Role;
use Mainfreme\UserProfile\Domain\User\ValueObject\UserId;

final class User
{
    private const MIN_PASSWORD_LENGTH = 8;

    private function __construct(
        private UserId $id,
        private Email $email,
        private DisplayName $displayName,
        private Bio $bio,
        private Role $role,
        private ?string $passwordHash,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function register(
        Email $email,
        DisplayName $displayName,
        Role $role,
        Bio $bio,
        ?string $passwordHash = null,
        ?UserId $id = null,
        ?DateTimeImmutable $createdAt = null,
    ): self {
        $now = $createdAt ?? new DateTimeImmutable();

        return new self(
            $id ?? UserId::generate(),
            $email,
            $displayName,
            $bio,
            $role,
            $passwordHash,
            $now,
            $now,
        );
    }

    public static function restore(
        UserId $id,
        Email $email,
        DisplayName $displayName,
        Bio $bio,
        Role $role,
        ?string $passwordHash,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $email,
            $displayName,
            $bio,
            $role,
            $passwordHash,
            $createdAt,
            $updatedAt,
        );
    }

    public static function assertPlainPassword(?string $plainPassword): void
    {
        if (null === $plainPassword || '' === $plainPassword) {
            return;
        }

        if (mb_strlen($plainPassword) < self::MIN_PASSWORD_LENGTH) {
            throw InvalidPasswordException::tooShort(self::MIN_PASSWORD_LENGTH);
        }
    }

    public function updateProfile(Bio $bio, DisplayName $displayName): void
    {
        $this->bio = $bio;
        $this->displayName = $displayName;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function assignRole(Role $role): void
    {
        $this->role = $role;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function displayName(): DisplayName
    {
        return $this->displayName;
    }

    public function bio(): Bio
    {
        return $this->bio;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function passwordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
