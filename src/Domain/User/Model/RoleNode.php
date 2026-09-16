<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Model;

final readonly class RoleNode
{
    /**
     * @param list<RoleNode> $children
     */
    public function __construct(
        public string $role,
        public string $label,
        public array $children = [],
    ) {
    }

    /**
     * @return array{role: string, label: string, children: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'label' => $this->label,
            'children' => array_map(static fn (self $child): array => $child->toArray(), $this->children),
        ];
    }
}
