<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\DTO;

use SWH\UserProfile\Domain\User\Enum\OperationStatus;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Exception\UserNotFoundException;
use SWH\UserProfile\Domain\User\Model\RoleTree;

final readonly class RoleTreeResponse implements JsonHttpResponse
{
    /**
     * @param list<array{role: string, label: string, children: list<array<string, mixed>>}> $roles
     */
    public function __construct(
        public OperationStatus $status,
        public ?string $errorMessage,
        public array $roles,
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
            'roles' => $this->roles,
        ];
    }

    public function httpStatus(int $successCode): int
    {
        return OperationStatus::Success === $this->status ? $successCode : $this->errorStatus;
    }

    public static function success(RoleTree $tree): self
    {
        return new self(OperationStatus::Success, null, $tree->toArray());
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
