<?php

declare(strict_types=1);

namespace SWH\UserProfile\Tests\Unit\Infrastructure\Persistence;

use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\Model\User;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use SWH\UserProfile\Infrastructure\Persistence\FilesystemUserRepository;
use PHPUnit\Framework\TestCase;

final class FilesystemUserRepositoryTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir().'/user-profile-'.uniqid('', true);
        mkdir($this->root, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);

        parent::tearDown();
    }

    public function test_save_and_find_user_by_id_and_email(): void
    {
        $repository = new FilesystemUserRepository($this->root);
        $user = User::register(
            email: Email::fromString('jan@example.com'),
            displayName: DisplayName::fromString('Jan Kowalski'),
            role: UserRole::Admin,
            bio: Bio::fromString('Bio na dysku', 2000),
        );

        $repository->save($user);

        $byId = $repository->findById($user->id());
        $byEmail = $repository->findByEmail(Email::fromString('jan@example.com'));

        self::assertNotNull($byId);
        self::assertSame('Bio na dysku', $byId->bio()->toString());
        self::assertSame('ROLE_ADMIN', $byId->role()->toString());
        self::assertNotNull($byEmail);
        self::assertTrue($user->id()->equals($byEmail->id()));
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        if (false === $items) {
            return;
        }

        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $path = $directory.'/'.$item;

            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
