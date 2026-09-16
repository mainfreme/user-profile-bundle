<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\Group\Model;

use DateTimeImmutable;
use SWH\UserProfile\Domain\Group\Exception\InvalidGroupDescriptionException;
use SWH\UserProfile\Domain\Group\ValueObject\GroupId;
use SWH\UserProfile\Domain\Group\ValueObject\GroupName;
use SWH\UserProfile\Domain\User\ValueObject\Role;

final class Group
{
    private const DESCRIPTION_MAX_LENGTH = 500;

    private function __construct(
        private GroupId $id,
        private GroupName $name,
        private string $description,
        private ?Role $role,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        GroupName $name,
        string $description,
        ?Role $role,
        ?GroupId $id = null,
        ?DateTimeImmutable $createdAt = null,
    ): self {
        $now = $createdAt ?? new DateTimeImmutable();

        return new self(
            $id ?? GroupId::generate(),
            $name,
            self::normalizeDescription($description),
            $role,
            $now,
            $now,
        );
    }

    public static function restore(
        GroupId $id,
        GroupName $name,
        string $description,
        ?Role $role,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $name, $description, $role, $createdAt, $updatedAt);
    }

    public function id(): GroupId
    {
        return $this->id;
    }

    public function name(): GroupName
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function role(): ?Role
    {
        return $this->role;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private static function normalizeDescription(string $description): string
    {
        $trimmed = trim($description);

        if (mb_strlen($trimmed) > self::DESCRIPTION_MAX_LENGTH) {
            throw InvalidGroupDescriptionException::tooLong(self::DESCRIPTION_MAX_LENGTH);
        }

        return $trimmed;
    }
}
