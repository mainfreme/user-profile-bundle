<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Query\GetRoleTree;

use Mainfreme\UserProfile\Application\DTO\RoleTreeResponse;
use Mainfreme\UserProfile\Domain\User\Port\RoleCatalogInterface;
use Throwable;

final class GetRoleTreeHandler
{
    public function __construct(
        private readonly RoleCatalogInterface $roleCatalog,
    ) {
    }

    public function __invoke(GetRoleTreeQuery $query): RoleTreeResponse
    {
        try {
            return RoleTreeResponse::success($this->roleCatalog->tree());
        } catch (Throwable) {
            return RoleTreeResponse::error('An unexpected error occurred while reading the role tree.');
        }
    }
}
