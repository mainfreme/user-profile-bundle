<?php

declare(strict_types=1);

namespace Mainfreme\UserProfile\Application\Service;

use RuntimeException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class MessageBusDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus,
    ) {
    }

    public function dispatchCommand(object $command): mixed
    {
        return $this->dispatch($this->commandBus, $command);
    }

    public function dispatchQuery(object $query): mixed
    {
        return $this->dispatch($this->queryBus, $query);
    }

    private function dispatch(MessageBusInterface $bus, object $message): mixed
    {
        $envelope = $bus->dispatch($message);
        $handledStamp = $envelope->last(HandledStamp::class);

        if (!$handledStamp instanceof HandledStamp) {
            throw new RuntimeException('Message was not handled.');
        }

        return $handledStamp->getResult();
    }
}
