<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Domain\User\ValueObject;

use SWH\UserProfile\Domain\User\Exception\InvalidBioException;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use PHPUnit\Framework\TestCase;

final class BioTest extends TestCase
{
    public function test_creates_trimmed_bio(): void
    {
        $bio = Bio::fromString('  Lubię Symfony  ', 2000);

        self::assertSame('Lubię Symfony', $bio->toString());
        self::assertFalse($bio->isEmpty());
    }

    public function test_allows_empty_bio(): void
    {
        $bio = Bio::fromString('   ', 2000);

        self::assertTrue($bio->isEmpty());
    }

    public function test_rejects_bio_longer_than_limit(): void
    {
        $this->expectException(InvalidBioException::class);

        Bio::fromString(str_repeat('a', 21), 20);
    }
}
