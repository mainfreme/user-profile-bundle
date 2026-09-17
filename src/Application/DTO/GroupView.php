<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\DTO;

use SWH\UserProfile\Domain\Group\Model\Group;

final readonly class GroupView
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public ?string $role,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromGroup(Group $group): self
    {
        return new self(
            $group->id()->toString(),
            $group->name()->toString(),
            $group->description()->toString(),
            $group->role()?->toString(),
            $group->createdAt()->format(\DATE_ATOM),
            $group->updatedAt()->format(\DATE_ATOM),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'role' => $this->role,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
