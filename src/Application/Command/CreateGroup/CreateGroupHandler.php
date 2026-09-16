<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\CreateGroup;

use SWH\UserProfile\Application\DTO\GroupResponse;
use SWH\UserProfile\Domain\Group\Exception\GroupAlreadyExistsException;
use SWH\UserProfile\Domain\Group\Model\Group;
use SWH\UserProfile\Domain\Group\Port\GroupRepositoryInterface;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Port\RoleCatalogInterface;
use SWH\UserProfile\Domain\User\ValueObject\Role;
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

            if (null !== $this->groupRepository->findByName($command->name)) {
                throw GroupAlreadyExistsException::forName($command->name->toString());
            }

            $role = null;
            if (null !== $command->role) {
                $role = Role::fromString($command->role->value, $this->roleCatalog->tree()->flattenCodes());
            }

            $group = Group::create(
                name: $command->name,
                description: $command->description ?? '',
                role: $role,
            );

            $this->groupRepository->save($group);

            return GroupResponse::success($group);
        } catch (UserDomainException $exception) {
            return GroupResponse::fromException($exception);
        } catch (Throwable) {
            return GroupResponse::error('An unexpected error occurred while creating the group.');
        }
    }
}
