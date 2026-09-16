<?php

declare(strict_types=1);

namespace SWH\UserProfile\Infrastructure\Persistence;

use SWH\UserProfile\Domain\Group\Model\Group;
use SWH\UserProfile\Domain\Group\Port\GroupRepositoryInterface;
use SWH\UserProfile\Domain\Group\ValueObject\GroupId;
use SWH\UserProfile\Domain\Group\ValueObject\GroupName;

final class InMemoryGroupRepository implements GroupRepositoryInterface
{
    /**
     * @var array<string, Group>
     */
    private array $groups = [];

    public function save(Group $group): void
    {
        $this->groups[$group->id()->toString()] = $group;
    }

    public function findById(GroupId $groupId): ?Group
    {
        return $this->groups[$groupId->toString()] ?? null;
    }

    public function findByName(GroupName $name): ?Group
    {
        foreach ($this->groups as $group) {
            if ($group->name()->normalized() === $name->normalized()) {
                return $group;
            }
        }

        return null;
    }

    public function findAll(): array
    {
        return array_values($this->groups);
    }
}
