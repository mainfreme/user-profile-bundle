<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Domain\Group\ValueObject;

use SWH\UserProfile\Domain\Group\Exception\InvalidGroupIdException;
use SWH\UserProfile\Domain\Group\ValueObject\GroupId;
use SWH\UserProfile\Domain\User\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class GroupIdTest extends TestCase
{
    public function test_generates_valid_uuid(): void
    {
        $groupId = GroupId::generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $groupId->toString(),
        );
    }

    public function test_creates_from_valid_string(): void
    {
        $groupId = GroupId::fromString('550e8400-e29b-41d4-a716-446655440000');

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $groupId->toString());
    }

    public function test_rejects_invalid_format(): void
    {
        $this->expectException(InvalidGroupIdException::class);

        GroupId::fromString('not-a-uuid');
    }

    public function test_does_not_equal_user_id_with_same_value(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        self::assertFalse(GroupId::fromString($uuid)->equals(UserId::fromString($uuid)));
    }
}
