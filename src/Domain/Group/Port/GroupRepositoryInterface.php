<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\Group\Port;

use Mainfreme\UserProfile\Domain\Group\Model\Group;
use Mainfreme\UserProfile\Domain\Group\ValueObject\GroupId;
use Mainfreme\UserProfile\Domain\Group\ValueObject\GroupName;

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
