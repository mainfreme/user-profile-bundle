<?php

declare(strict_types=1);

namespace SWH\UserProfile\Application\DTO;

interface JsonHttpResponse
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    public function httpStatus(int $successCode): int;
}
