<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Command\UpdateProfile;

final readonly class UpdateProfileCommand
{
    public function __construct(
        public string $userId,
        public string $bio,
        public ?string $displayName = null,
    ) {
    }
}
