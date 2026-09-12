<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Infrastructure\Persistence;

use DateTimeImmutable;
use Mainfreme\UserProfile\Domain\Group\Model\Group;
use Mainfreme\UserProfile\Domain\Group\Port\GroupRepositoryInterface;
use Mainfreme\UserProfile\Domain\Group\ValueObject\GroupId;
use Mainfreme\UserProfile\Domain\Group\ValueObject\GroupName;
use Mainfreme\UserProfile\Domain\User\ValueObject\Role;
use RuntimeException;

final class FilesystemGroupRepository implements GroupRepositoryInterface
{
    public function __construct(
        private readonly string $rootDirectory,
    ) {
    }

    public function save(Group $group): void
    {
        $this->ensureDirectory();

        $payload = [
            'id' => $group->id()->toString(),
            'name' => $group->name()->toString(),
            'description' => $group->description(),
            'role' => $group->role()?->toString(),
            'createdAt' => $group->createdAt()->format(\DATE_ATOM),
            'updatedAt' => $group->updatedAt()->format(\DATE_ATOM),
        ];

        $this->writeJson($this->groupFile($group->id()->toString()), $payload);
    }

    public function findById(GroupId $groupId): ?Group
    {
        $path = $this->groupFile($groupId->toString());

        if (!is_file($path)) {
            return null;
        }

        return $this->hydrate($this->readJson($path));
    }

    public function findByName(GroupName $name): ?Group
    {
        foreach ($this->findAll() as $group) {
            if ($group->name()->normalized() === $name->normalized()) {
                return $group;
            }
        }

        return null;
    }

    public function findAll(): array
    {
        $this->ensureDirectory();

        $files = glob($this->rootDirectory.'/*.json') ?: [];
        $groups = [];

        foreach ($files as $file) {
            $groups[] = $this->hydrate($this->readJson($file));
        }

        return $groups;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hydrate(array $data): Group
    {
        $createdAt = DateTimeImmutable::createFromFormat(\DATE_ATOM, (string) $data['createdAt']);
        $updatedAt = DateTimeImmutable::createFromFormat(\DATE_ATOM, (string) $data['updatedAt']);

        if (false === $createdAt || false === $updatedAt) {
            throw new RuntimeException('Stored group timestamps are invalid.');
        }

        $roleValue = $data['role'] ?? null;

        return Group::restore(
            id: GroupId::fromString((string) $data['id']),
            name: GroupName::fromString((string) $data['name']),
            description: (string) ($data['description'] ?? ''),
            role: \is_string($roleValue) && '' !== $roleValue ? Role::restore($roleValue) : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
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

    private function groupFile(string $groupId): string
    {
        return $this->rootDirectory.'/'.$groupId.'.json';
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
