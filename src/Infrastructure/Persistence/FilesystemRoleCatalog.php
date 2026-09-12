<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Infrastructure\Persistence;

use Mainfreme\UserProfile\Domain\User\Model\RoleTree;
use Mainfreme\UserProfile\Domain\User\Port\RoleCatalogInterface;
use RuntimeException;

final class FilesystemRoleCatalog implements RoleCatalogInterface
{
    private const FILE_NAME = 'roles.json';

    /**
     * @param list<array<string, mixed>> $seedConfig
     */
    public function __construct(
        private readonly string $rootDirectory,
        private readonly array $seedConfig,
    ) {
    }

    public function tree(): RoleTree
    {
        $path = $this->filePath();

        if (!is_file($path)) {
            $tree = RoleTree::fromConfig($this->seedConfig);
            $this->persist($tree);

            return $tree;
        }

        return RoleTree::fromConfig($this->readJson($path));
    }

    public function add(string $role, string $label, ?string $parentRole): RoleTree
    {
        $tree = $this->tree()->withAddedNode($role, $label, $parentRole);
        $this->persist($tree);

        return $tree;
    }

    private function persist(RoleTree $tree): void
    {
        $this->ensureDirectory();
        $this->writeJson($this->filePath(), $tree->toArray());
    }

    /**
     * @return list<mixed>
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

        return array_values($decoded);
    }

    /**
     * @param list<array<string, mixed>> $data
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

    private function filePath(): string
    {
        return $this->rootDirectory.'/'.self::FILE_NAME;
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
