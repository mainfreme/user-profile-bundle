<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\DTO;

use SWH\UserProfile\Domain\User\Enum\OperationStatus;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Exception\UserNotFoundException;
use SWH\UserProfile\Domain\User\Model\User;

final readonly class UserListResponse implements JsonHttpResponse
{
    /**
     * @param list<UserView> $users
     */
    public function __construct(
        public OperationStatus $status,
        public ?string $errorMessage,
        public array $users,
        public int $errorStatus = 500,
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

    public function httpStatus(int $successCode): int
    {
        return OperationStatus::Success === $this->status ? $successCode : $this->errorStatus;
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

    public static function error(string $errorMessage, int $errorStatus = 500): self
    {
        return new self(OperationStatus::Error, $errorMessage, [], $errorStatus);
    }

    public static function fromException(UserDomainException $exception): self
    {
        return self::error(
            $exception->getMessage(),
            $exception instanceof UserNotFoundException ? 404 : 400,
        );
    }
}
