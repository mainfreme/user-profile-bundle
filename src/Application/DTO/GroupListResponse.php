<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\DTO;

use Mainfreme\UserProfile\Domain\Group\Model\Group;
use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;

final readonly class GroupListResponse
{
    /**
     * @param list<GroupView> $groups
     */
    public function __construct(
        public OperationStatus $status,
        public ?string $errorMessage,
        public array $groups,
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

    public static function error(string $errorMessage): self
    {
        return new self(OperationStatus::Error, $errorMessage, []);
    }
}
