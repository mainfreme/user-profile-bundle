<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\DTO;

use SWH\UserProfile\Domain\User\Model\User;

final readonly class UserView
{
    public function __construct(
        public string $id,
        public string $email,
        public string $displayName,
        public string $bio,
        public string $role,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromUser(User $user): self
    {
        return new self(
            $user->id()->toString(),
            $user->email()->toString(),
            $user->displayName()->toString(),
            $user->bio()->toString(),
            $user->role()->toString(),
            $user->createdAt()->format(\DATE_ATOM),
            $user->updatedAt()->format(\DATE_ATOM),
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'displayName' => $this->displayName,
            'bio' => $this->bio,
            'role' => $this->role,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
