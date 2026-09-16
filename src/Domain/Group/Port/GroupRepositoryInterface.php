<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\Group\Port;

use SWH\UserProfile\Domain\Group\Model\Group;
use SWH\UserProfile\Domain\Group\ValueObject\GroupId;
use SWH\UserProfile\Domain\Group\ValueObject\GroupName;

interface GroupRepositoryInterface
{
    public function save(Group $group): void;

    public function findById(GroupId $groupId): ?Group;

    public function findByName(GroupName $name): ?Group;

    /**
     * @return list<Group>
     */
    public function findAll(): array;
}
