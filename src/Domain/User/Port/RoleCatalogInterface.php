<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Domain\User\Port;

use Mainfreme\UserProfile\Domain\User\Model\RoleTree;

interface RoleCatalogInterface
{
    public function tree(): RoleTree;

    public function add(string $role, string $label, ?string $parentRole): RoleTree;
}
