<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\CreateRole;

use SWH\UserProfile\Application\DTO\RoleTreeResponse;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Port\RoleCatalogInterface;
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
            return RoleTreeResponse::fromException($exception);
        } catch (Throwable) {
            return RoleTreeResponse::error('An unexpected error occurred while creating the role.');
        }
    }
}
