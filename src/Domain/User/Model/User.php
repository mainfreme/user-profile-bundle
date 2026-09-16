<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Model;

use DateTimeImmutable;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\ValueObject\UserId;

final class User
{
    private function __construct(
        private UserId $id,
        private Email $email,
        private DisplayName $displayName,
        private Bio $bio,
        private UserRole $role,
        private ?string $passwordHash,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function register(
        Email $email,
        DisplayName $displayName,
        UserRole $role,
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
        UserRole $role,
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

    public function updateProfile(Bio $bio, DisplayName $displayName): void
    {
        $this->bio = $bio;
        $this->displayName = $displayName;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function assignRole(UserRole $role): void
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

    public function role(): UserRole
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
