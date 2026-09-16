<?php

declare(strict_types=1);

namespace SWH\UserProfile\Infrastructure\Http\EventListener;

use SWH\UserProfile\Application\DTO\UserResponse;
use SWH\UserProfile\Domain\User\Exception\UserDomainException;
use SWH\UserProfile\Domain\User\Exception\UserNotFoundException;
use SWH\UserProfile\Infrastructure\Http\Exception\InvalidJsonBodyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class UserProfileExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof UserNotFoundException) {
            $event->setResponse(new JsonResponse(
                UserResponse::error($exception->getMessage())->toArray(),
                Response::HTTP_NOT_FOUND,
            ));

            return;
        }

        if ($exception instanceof UserDomainException || $exception instanceof InvalidJsonBodyException) {
            $event->setResponse(new JsonResponse(
                UserResponse::error($exception->getMessage())->toArray(),
                Response::HTTP_BAD_REQUEST,
            ));
        }
    }
}
