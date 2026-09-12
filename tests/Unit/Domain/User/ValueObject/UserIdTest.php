<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Tests\Unit\Domain\User\ValueObject;

use Mainfreme\UserProfile\Domain\User\Exception\InvalidUserIdException;
use Mainfreme\UserProfile\Domain\User\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    public function test_generates_valid_uuid(): void
    {
        $userId = UserId::generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $userId->toString(),
        );
    }

    public function test_creates_from_valid_string(): void
    {
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $userId->toString());
    }

    public function test_rejects_invalid_format(): void
    {
        $this->expectException(InvalidUserIdException::class);

        UserId::fromString('not-a-uuid');
    }
}
