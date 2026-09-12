<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\DTO;

use Mainfreme\UserProfile\Domain\User\Enum\OperationStatus;
use Mainfreme\UserProfile\Domain\User\Model\RoleTree;

final readonly class RoleTreeResponse
{
    /**
     * @param list<array{role: string, label: string, children: list<array<string, mixed>>}> $roles
     */
    public function __construct(
        public OperationStatus $status,
        public ?string $errorMessage,
        public array $roles,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'errorMessage' => $this->errorMessage,
            'roles' => $this->roles,
        ];
    }

    public static function success(RoleTree $tree): self
    {
        return new self(OperationStatus::Success, null, $tree->toArray());
    }

    public static function error(string $errorMessage): self
    {
        return new self(OperationStatus::Error, $errorMessage, []);
    }
}
