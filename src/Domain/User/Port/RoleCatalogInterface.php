<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Port;

use SWH\UserProfile\Domain\User\Model\RoleTree;

interface RoleCatalogInterface
{
    public function tree(): RoleTree;

    public function add(string $role, string $label, ?string $parentRole): RoleTree;
}
