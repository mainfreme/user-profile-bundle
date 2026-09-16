<?php

declare(strict_types=1);

namespace SWH\UserProfile\Infrastructure\Http\Exception;

use RuntimeException;

final class InvalidJsonBodyException extends RuntimeException
{
    public static function required(): self
    {
        return new self('JSON body is required.');
    }

    public static function invalid(): self
    {
        return new self('JSON body is invalid.');
    }

    public static function notObject(): self
    {
        return new self('JSON body must be an object.');
    }
}
