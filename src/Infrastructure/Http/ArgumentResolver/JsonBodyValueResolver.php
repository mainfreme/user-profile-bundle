<?php

declare(strict_types=1);

namespace SWH\UserProfile\Infrastructure\Http\ArgumentResolver;

use SWH\UserProfile\Infrastructure\Http\Request\JsonBody;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class JsonBodyValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (JsonBody::class !== $argument->getType()) {
            return [];
        }

        yield JsonBody::fromRequest($request);
    }
}
