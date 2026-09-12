<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Infrastructure\Security;

use Mainfreme\UserProfile\Domain\User\Port\PasswordHasherInterface;

final class NativePasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return password_hash($plainPassword, \PASSWORD_DEFAULT);
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}
