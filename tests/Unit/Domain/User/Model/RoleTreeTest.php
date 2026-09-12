<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Tests\Unit\Domain\User\Model;

use InvalidArgumentException;
use Mainfreme\UserProfile\Domain\User\Exception\InvalidRoleException;
use Mainfreme\UserProfile\Domain\User\Model\RoleTree;
use PHPUnit\Framework\TestCase;

final class RoleTreeTest extends TestCase
{
    public function test_default_tree_flattens_hierarchy(): void
    {
        $tree = RoleTree::fromConfig(RoleTree::defaultConfig());

        self::assertSame(['ROLE_ADMIN', 'ROLE_MODERATOR', 'ROLE_USER'], $tree->flattenCodes());
        self::assertSame('Administrator', $tree->labels()['ROLE_ADMIN']);
        self::assertSame('Użytkownik', $tree->labels()['ROLE_USER']);
        self::assertCount(1, $tree->roots());
        self::assertSame('ROLE_ADMIN', $tree->roots()[0]->role);
        self::assertSame('ROLE_MODERATOR', $tree->roots()[0]->children[0]->role);
        self::assertSame('ROLE_USER', $tree->roots()[0]->children[0]->children[0]->role);
    }

    public function test_adds_role_under_parent(): void
    {
        $tree = RoleTree::fromConfig(RoleTree::defaultConfig())
            ->withAddedNode('ROLE_EDITOR', 'Redaktor', 'ROLE_MODERATOR');

        self::assertContains('ROLE_EDITOR', $tree->flattenCodes());
        self::assertSame('Redaktor', $tree->labels()['ROLE_EDITOR']);
        self::assertSame('ROLE_EDITOR', $tree->roots()[0]->children[0]->children[1]->role);
    }

    public function test_adds_root_role_without_parent(): void
    {
        $tree = RoleTree::fromConfig(RoleTree::defaultConfig())
            ->withAddedNode('ROLE_GUEST', 'Gość');

        self::assertCount(2, $tree->roots());
        self::assertSame('ROLE_GUEST', $tree->roots()[1]->role);
    }

    public function test_rejects_duplicate_roles(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RoleTree::fromConfig([
            [
                'role' => 'ROLE_ADMIN',
                'label' => 'Administrator',
                'children' => [
                    ['role' => 'ROLE_ADMIN', 'label' => 'Duplikat', 'children' => []],
                ],
            ],
        ]);
    }

    public function test_rejects_role_without_prefix(): void
    {
        $this->expectException(InvalidRoleException::class);

        RoleTree::fromConfig([
            ['role' => 'ADMIN', 'label' => 'Admin', 'children' => []],
        ]);
    }

    public function test_flat_list_fallback(): void
    {
        $tree = RoleTree::fromFlatList(['ROLE_USER', 'ROLE_ADMIN']);

        self::assertSame(['ROLE_USER', 'ROLE_ADMIN'], $tree->flattenCodes());
        self::assertCount(2, $tree->roots());
    }
}
