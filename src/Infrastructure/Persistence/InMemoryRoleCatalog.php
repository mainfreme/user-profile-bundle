<?php

declare(strict_types=1);

namespace SWH\UserProfile\Infrastructure\Persistence;

use SWH\UserProfile\Domain\User\Model\RoleTree;
use SWH\UserProfile\Domain\User\Port\RoleCatalogInterface;
use SWH\UserProfile\Domain\User\ValueObject\Role;
use SWH\UserProfile\Domain\User\ValueObject\RoleLabel;

final class InMemoryRoleCatalog implements RoleCatalogInterface
{
    private RoleTree $tree;

    /**
     * @param list<mixed>|RoleTree $seed
     */
    public function __construct(array|RoleTree $seed)
    {
        $this->tree = $seed instanceof RoleTree ? $seed : RoleTree::fromConfig($seed);
    }

    public function tree(): RoleTree
    {
        return $this->tree;
    }

    public function add(Role $role, RoleLabel $label, ?Role $parentRole): RoleTree
    {
        $this->tree = $this->tree->withAddedNode($role, $label, $parentRole);

        return $this->tree;
    }
}
