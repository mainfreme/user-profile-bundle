<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Command\CreateGroup;

use Mainfreme\UserProfile\Application\DTO\GroupResponse;
use Mainfreme\UserProfile\Domain\Group\Exception\GroupAlreadyExistsException;
use Mainfreme\UserProfile\Domain\Group\Model\Group;
use Mainfreme\UserProfile\Domain\Group\Port\GroupRepositoryInterface;
use Mainfreme\UserProfile\Domain\Group\ValueObject\GroupName;
use Mainfreme\UserProfile\Domain\User\Exception\UserDomainException;
use Mainfreme\UserProfile\Domain\User\Port\RoleCatalogInterface;
use Mainfreme\UserProfile\Domain\User\ValueObject\Role;
use Throwable;

final class CreateGroupHandler
{
    public function __construct(
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly RoleCatalogInterface $roleCatalog,
    ) {
    }

    public function __invoke(CreateGroupCommand $command): GroupResponse
    {
        try {
            $name = GroupName::fromString($command->name);

            if (null !== $this->groupRepository->findByName($name)) {
                throw GroupAlreadyExistsException::forName($name->toString());
            }

            $role = null;
            if (null !== $command->role && '' !== trim($command->role)) {
                $role = Role::fromString($command->role, $this->roleCatalog->tree()->flattenCodes());
            }

            $group = Group::create(
                name: $name,
                description: $command->description ?? '',
                role: $role,
            );

            $this->groupRepository->save($group);

            return GroupResponse::success($group);
        } catch (UserDomainException $exception) {
            return GroupResponse::error($exception->getMessage());
        } catch (Throwable) {
            return GroupResponse::error('An unexpected error occurred while creating the group.');
        }
    }
}
