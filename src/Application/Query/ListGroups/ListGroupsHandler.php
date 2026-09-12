<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Query\ListGroups;

use Mainfreme\UserProfile\Application\DTO\GroupListResponse;
use Mainfreme\UserProfile\Domain\Group\Port\GroupRepositoryInterface;
use Mainfreme\UserProfile\Domain\User\Exception\UserDomainException;
use Throwable;

final class ListGroupsHandler
{
    public function __construct(
        private readonly GroupRepositoryInterface $groupRepository,
    ) {
    }

    public function __invoke(ListGroupsQuery $query): GroupListResponse
    {
        try {
            return GroupListResponse::success($this->groupRepository->findAll());
        } catch (UserDomainException $exception) {
            return GroupListResponse::error($exception->getMessage());
        } catch (Throwable) {
            return GroupListResponse::error('An unexpected error occurred while listing groups.');
        }
    }
}
