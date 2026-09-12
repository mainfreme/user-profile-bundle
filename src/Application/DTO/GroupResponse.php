<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\DTO;

use Mainfreme\UserProfile\Domain\Group\Model\Group;
use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;

final readonly class GroupResponse
{
    public function __construct(
        public OperationStatus $status,
        public ?string $errorMessage,
        public ?GroupView $group,
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
            'group' => $this->group?->toArray(),
        ];
    }

    public static function success(Group $group): self
    {
        return new self(OperationStatus::Success, null, GroupView::fromGroup($group));
    }

    public static function error(string $errorMessage): self
    {
        return new self(OperationStatus::Error, $errorMessage, null);
    }
}
