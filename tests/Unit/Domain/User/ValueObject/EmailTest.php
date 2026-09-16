<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Domain\User\ValueObject;

use SWH\UserProfile\Domain\User\Exception\InvalidEmailException;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function test_creates_normalized_email(): void
    {
        $email = Email::fromString('Jan.Kowalski@Example.COM');

        self::assertSame('jan.kowalski@example.com', $email->toString());
    }

    public function test_rejects_empty_email(): void
    {
        $this->expectException(InvalidEmailException::class);

        Email::fromString('   ');
    }

    public function test_rejects_invalid_format(): void
    {
        $this->expectException(InvalidEmailException::class);

        Email::fromString('not-an-email');
    }
}
