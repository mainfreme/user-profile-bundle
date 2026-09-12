<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Command\CreateRole;

use Mainfreme\UserProfile\Application\DTO\RoleTreeResponse;
use Mainfreme\UserProfile\Domain\User\Exception\UserDomainException;
use Mainfreme\UserProfile\Domain\User\Port\RoleCatalogInterface;
use Throwable;

final class CreateRoleHandler
{
    public function __construct(
        private readonly RoleCatalogInterface $roleCatalog,
    ) {
    }

    public function __invoke(CreateRoleCommand $command): RoleTreeResponse
    {
        try {
            $tree = $this->roleCatalog->add($command->role, $command->label, $command->parentRole);

            return RoleTreeResponse::success($tree);
        } catch (UserDomainException $exception) {
            return RoleTreeResponse::error($exception->getMessage());
        } catch (Throwable) {
            return RoleTreeResponse::error('An unexpected error occurred while creating the role.');
        }
    }
}
