<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Query\GetUser;

use SWH\UserProfile\Domain\User\ValueObject\UserId;

final readonly class GetUserQuery
{
    public function __construct(
        public UserId $userId,
    ) {
    }
}
