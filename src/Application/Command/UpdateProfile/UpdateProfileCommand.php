<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\Command\UpdateProfile;

use SWH\UserProfile\Domain\User\ValueObject\Bio;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use SWH\UserProfile\Domain\User\ValueObject\UserId;

final readonly class UpdateProfileCommand
{
    public function __construct(
        public UserId $userId,
        public ?Bio $bio = null,
        public ?DisplayName $displayName = null,
    ) {
    }
}
