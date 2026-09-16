<?php

declare(strict_types=1);

namespace SWH\UserProfile\Domain\User\Enum;

enum OperationStatus: string
{
    case Success = 'success';
    case Error = 'error';
}
