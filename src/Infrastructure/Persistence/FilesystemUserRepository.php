<?php

declare(strict_types=1);

namespace SWH\UserProfile\Infrastructure\Persistence;

use DateTimeImmutable;
use SWH\UserProfile\Domain\User\Enum\UserRole;
use SWH\UserProfile\Domain\User\Model\User;
use SWH\UserProfile\Domain\User\Port\UserRepositoryInterface;
use SWH\UserProfile\Domain\User\ValueObject\Bio;
use SWH\UserProfile\Domain\User\ValueObject\DisplayName;
use SWH\UserProfile\Domain\User\ValueObject\Email;
use SWH\UserProfile\Domain\User\ValueObject\UserId;
use RuntimeException;

final class FilesystemUserRepository implements UserRepositoryInterface
{
    private const INDEX_FILE = '_index.json';

    public function __construct(
        private readonly string $rootDirectory,
    ) {
    }

    public function save(User $user): void
    {
        $this->ensureDirectory();

        $payload = [
            'id' => $user->id()->toString(),
            'email' => $user->email()->toString(),
            'displayName' => $user->displayName()->toString(),
            'bio' => $user->bio()->toString(),
            'role' => $user->role()->toString(),
            'passwordHash' => $user->passwordHash(),
            'createdAt' => $user->createdAt()->format(\DATE_ATOM),
            'updatedAt' => $user->updatedAt()->format(\DATE_ATOM),
        ];

        $this->writeJson($this->userFile($user->id()), $payload);

        $index = $this->readIndex();
        $index[$user->email()->toString()] = $user->id()->toString();
        $this->writeJson($this->indexFile(), $index);
    }

    public function findById(UserId $userId): ?User
    {
        $path = $this->userFile($userId);

        if (!is_file($path)) {
            return null;
        }

        return $this->hydrate($this->readJson($path));
    }

    public function findByEmail(Email $email): ?User
    {
        $index = $this->readIndex();
        $userId = $index[$email->toString()] ?? null;

        if (!\is_string($userId)) {
            return null;
        }

        return $this->findById(UserId::fromString($userId));
    }

    public function findAll(?string $role = null): array
    {
        $this->ensureDirectory();

        $files = glob($this->rootDirectory.'/*.json') ?: [];
        $users = [];

        foreach ($files as $file) {
            if (self::INDEX_FILE === basename($file) || 'roles.json' === basename($file)) {
                continue;
            }

            $user = $this->hydrate($this->readJson($file));

            if (null !== $role && $user->role()->toString() !== $role) {
                continue;
            }

            $users[] = $user;
        }

        return $users;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hydrate(array $data): User
    {
        $createdAt = DateTimeImmutable::createFromFormat(\DATE_ATOM, (string) $data['createdAt']);
        $updatedAt = DateTimeImmutable::createFromFormat(\DATE_ATOM, (string) $data['updatedAt']);

        if (false === $createdAt || false === $updatedAt) {
            throw new RuntimeException('Stored user timestamps are invalid.');
        }

        return User::restore(
            id: UserId::fromString((string) $data['id']),
            email: Email::fromString((string) $data['email']),
            displayName: DisplayName::fromString((string) $data['displayName']),
            bio: Bio::fromString((string) $data['bio'], 1_000_000),
            role: UserRole::fromString((string) $data['role']),
            passwordHash: isset($data['passwordHash']) && \is_string($data['passwordHash']) ? $data['passwordHash'] : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    /**
     * @return array<string, string>
     */
    private function readIndex(): array
    {
        if (!is_file($this->indexFile())) {
            return [];
        }

        /** @var array<string, string> $index */
        $index = $this->readJson($this->indexFile());

        return $index;
    }

    /**
     * @return array<string, mixed>
     */
    private function readJson(string $path): array
    {
        $contents = file_get_contents($path);

        if (false === $contents) {
            throw new RuntimeException(\sprintf('Unable to read file "%s".', $path));
        }

        $decoded = json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);

        if (!\is_array($decoded)) {
            throw new RuntimeException(\sprintf('Invalid JSON structure in "%s".', $path));
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function writeJson(string $path, array $data): void
    {
        $encoded = json_encode($data, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
        $temporaryPath = $path.'.tmp';

        if (false === file_put_contents($temporaryPath, $encoded)) {
            throw new RuntimeException(\sprintf('Unable to write file "%s".', $path));
        }

        if (!rename($temporaryPath, $path)) {
            throw new RuntimeException(\sprintf('Unable to persist file "%s".', $path));
        }
    }

    private function userFile(UserId $userId): string
    {
        return $this->rootDirectory.'/'.$userId->toString().'.json';
    }

    private function indexFile(): string
    {
        return $this->rootDirectory.'/'.self::INDEX_FILE;
    }

    private function ensureDirectory(): void
    {
        if (is_dir($this->rootDirectory)) {
            return;
        }

        if (!mkdir($this->rootDirectory, 0777, true) && !is_dir($this->rootDirectory)) {
            throw new RuntimeException(\sprintf('Unable to create directory "%s".', $this->rootDirectory));
        }
    }
}
