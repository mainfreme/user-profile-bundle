<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Port;

use SWH\UserProfile\Domain\User\Model\RoleTree;
use SWH\UserProfile\Domain\User\ValueObject\Role;
use SWH\UserProfile\Domain\User\ValueObject\RoleLabel;

interface RoleCatalogInterface
{
    public function tree(): RoleTree;

    public function add(Role $role, RoleLabel $label, ?Role $parentRole): RoleTree;
}
