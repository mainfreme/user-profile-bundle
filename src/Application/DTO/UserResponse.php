<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\DTO;

use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;
use Mainfreme\UserProfile\Domain\User\Model\User;

final readonly class UserResponse
{
    public function __construct(
        public OperationStatus $status,
        public ?string $errorMessage,
        public ?UserView $user,
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
            'user' => $this->user?->toArray(),
        ];
    }

    public static function success(User $user): self
    {
        return new self(OperationStatus::Success, null, UserView::fromUser($user));
    }

    public static function error(string $errorMessage): self
    {
        return new self(OperationStatus::Error, $errorMessage, null);
    }
}
