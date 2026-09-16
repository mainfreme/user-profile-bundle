<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Model;

use InvalidArgumentException;
use SWH\UserProfile\Domain\User\Exception\InvalidRoleException;

final readonly class RoleTree
{
    /**
     * @param list<RoleNode> $roots
     */
    public function __construct(
        private array $roots,
    ) {
    }

    /**
     * @return list<array{role: string, label: string, children: list<array<string, mixed>>}>
     */
    public static function defaultConfig(): array
    {
        return [
            [
                'role' => 'ROLE_ADMIN',
                'label' => 'Administrator',
                'children' => [
                    [
                        'role' => 'ROLE_MODERATOR',
                        'label' => 'Moderator',
                        'children' => [
                            [
                                'role' => 'ROLE_USER',
                                'label' => 'Użytkownik',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param list<mixed> $config
     */
    public static function fromConfig(array $config): self
    {
        $seen = [];

        return new self(self::normalizeNodes($config, $seen));
    }

    /**
     * @param list<string> $roles
     */
    public static function fromFlatList(array $roles): self
    {
        $nodes = [];

        foreach ($roles as $role) {
            $normalized = strtoupper(trim($role));

            if ('' === $normalized) {
                continue;
            }

            $nodes[] = new RoleNode($normalized, self::defaultLabel($normalized), []);
        }

        return new self($nodes);
    }

    public function isEmpty(): bool
    {
        return [] === $this->roots;
    }

    /**
     * @return list<RoleNode>
     */
    public function roots(): array
    {
        return $this->roots;
    }

    /**
     * @return list<string>
     */
    public function flattenCodes(): array
    {
        $codes = [];
        $this->collectCodes($this->roots, $codes);

        return $codes;
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        $labels = [];
        $this->collectLabels($this->roots, $labels);

        return $labels;
    }

    /**
     * @return list<array{role: string, label: string, children: list<array<string, mixed>>}>
     */
    public function toArray(): array
    {
        return array_map(static fn (RoleNode $node): array => $node->toArray(), $this->roots);
    }

    public function withAddedNode(string $role, string $label, ?string $parentRole = null): self
    {
        $normalized = strtoupper(trim($role));
        $normalizedLabel = trim($label);
        $normalizedParent = null === $parentRole || '' === trim($parentRole)
            ? null
            : strtoupper(trim($parentRole));

        if ('' === $normalized) {
            throw InvalidRoleException::empty();
        }

        if (!str_starts_with($normalized, 'ROLE_')) {
            throw InvalidRoleException::invalidPrefix($role);
        }

        if ('' === $normalizedLabel) {
            throw InvalidRoleException::invalidLabel();
        }

        if (\in_array($normalized, $this->flattenCodes(), true)) {
            throw InvalidRoleException::alreadyExists($normalized);
        }

        if (null === $normalizedParent) {
            return new self([...$this->roots, new RoleNode($normalized, $normalizedLabel, [])]);
        }

        if (!\in_array($normalizedParent, $this->flattenCodes(), true)) {
            throw InvalidRoleException::parentNotFound($normalizedParent);
        }

        return new self($this->appendChild($this->roots, $normalized, $normalizedLabel, $normalizedParent));
    }

    /**
     * @param list<RoleNode> $nodes
     *
     * @return list<RoleNode>
     */
    private function appendChild(array $nodes, string $role, string $label, string $parentRole): array
    {
        $result = [];

        foreach ($nodes as $node) {
            $children = $this->appendChild($node->children, $role, $label, $parentRole);

            if ($node->role === $parentRole) {
                $children[] = new RoleNode($role, $label, []);
            }

            $result[] = new RoleNode($node->role, $node->label, $children);
        }

        return $result;
    }

    /**
     * @param list<mixed>         $nodes
     * @param array<string, true> $seen
     *
     * @return list<RoleNode>
     */
    private static function normalizeNodes(array $nodes, array &$seen): array
    {
        $result = [];

        foreach ($nodes as $node) {
            if (!\is_array($node)) {
                throw new InvalidArgumentException('Each role tree node must be an array.');
            }

            $role = strtoupper(trim((string) ($node['role'] ?? '')));
            $label = trim((string) ($node['label'] ?? ''));

            if ('' === $role) {
                throw InvalidRoleException::empty();
            }

            if (!str_starts_with($role, 'ROLE_')) {
                throw InvalidRoleException::invalidPrefix((string) ($node['role'] ?? ''));
            }

            if (isset($seen[$role])) {
                throw new InvalidArgumentException(\sprintf('Duplicate role "%s" in role tree.', $role));
            }

            $seen[$role] = true;

            if ('' === $label) {
                $label = self::defaultLabel($role);
            }

            $children = $node['children'] ?? [];

            if (!\is_array($children)) {
                throw new InvalidArgumentException(\sprintf('Children of role "%s" must be a list.', $role));
            }

            $result[] = new RoleNode($role, $label, self::normalizeNodes(array_values($children), $seen));
        }

        return $result;
    }

    private static function defaultLabel(string $role): string
    {
        return match ($role) {
            'ROLE_USER' => 'Użytkownik',
            'ROLE_MODERATOR' => 'Moderator',
            'ROLE_ADMIN' => 'Administrator',
            default => ucwords(strtolower(str_replace(['ROLE_', '_'], ['', ' '], $role))),
        };
    }

    /**
     * @param list<RoleNode> $nodes
     * @param list<string>   $codes
     */
    private function collectCodes(array $nodes, array &$codes): void
    {
        foreach ($nodes as $node) {
            $codes[] = $node->role;
            $this->collectCodes($node->children, $codes);
        }
    }

    /**
     * @param list<RoleNode>        $nodes
     * @param array<string, string> $labels
     */
    private function collectLabels(array $nodes, array &$labels): void
    {
        foreach ($nodes as $node) {
            $labels[$node->role] = $node->label;
            $this->collectLabels($node->children, $labels);
        }
    }
}
