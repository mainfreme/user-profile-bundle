<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\DTO;

use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;
use Mainfreme\UserProfile\Domain\User\Model\User;

final readonly class UserListResponse
{
    /**
     * @param list<UserView> $users
     */
    public function __construct(
        public OperationStatus $status,
        public ?string $errorMessage,
        public array $users,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'errorMessage' => $this->errorMessage,
            'users' => array_map(static fn (UserView $user): array => $user->toArray(), $this->users),
        ];
    }

    /**
     * @param list<User> $users
     */
    public static function success(array $users): self
    {
        return new self(
            OperationStatus::Success,
            null,
            array_map(static fn (User $user): UserView => UserView::fromUser($user), $users),
        );
    }

    public static function error(string $errorMessage): self
    {
        return new self(OperationStatus::Error, $errorMessage, []);
    }
}
