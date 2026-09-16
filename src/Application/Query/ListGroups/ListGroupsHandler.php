<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Query\ListGroups;

use SWH\UserProfile\Application\DTO\GroupListResponse;
use SWH\UserProfile\Domain\Group\Port\GroupRepositoryInterface;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
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
            return GroupListResponse::fromException($exception);
        } catch (Throwable) {
            return GroupListResponse::error('An unexpected error occurred while listing groups.');
        }
    }
}
