<?php

declare(strict_types=1);

namespace SWH\UserProfile\Infrastructure\Http\ArgumentResolver;

use SWH\UserProfile\Domain\User\ValueObject\UserId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class UserIdValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (UserId::class !== $argument->getType()) {
            return [];
        }

        $id = $request->attributes->get('id');

        if (!\is_string($id)) {
            return [];
        }

        yield UserId::fromString($id);
    }
}
