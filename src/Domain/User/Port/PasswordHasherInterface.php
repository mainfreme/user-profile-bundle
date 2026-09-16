<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Port;

interface PasswordHasherInterface
{
    public function hash(string $plainPassword): string;

    public function verify(string $hashedPassword, string $plainPassword): bool;
}
