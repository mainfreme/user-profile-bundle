<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\DTO;

use SWH\UserProfile\Domain\Group\Model\Group;
use SWH\UserProfile\Domain\User\Enum\OperationStatus;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Exception\UserNotFoundException;

final readonly class GroupListResponse implements JsonHttpResponse
{
    /**
     * @param list<GroupView> $groups
     */
    public function __construct(
        public OperationStatus $status,
        public ?string $errorMessage,
        public array $groups,
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
            'groups' => array_map(static fn (GroupView $group): array => $group->toArray(), $this->groups),
        ];
    }

    public function httpStatus(int $successCode): int
    {
        return OperationStatus::Success === $this->status ? $successCode : $this->errorStatus;
    }

    /**
     * @param list<Group> $groups
     */
    public static function success(array $groups): self
    {
        return new self(
            OperationStatus::Success,
            null,
            array_map(static fn (Group $group): GroupView => GroupView::fromGroup($group), $groups),
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
