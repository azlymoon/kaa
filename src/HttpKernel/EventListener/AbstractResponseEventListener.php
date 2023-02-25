<?php

namespace Kaa\HttpKernel\EventListener;

use Kaa\EventDispatcher\EventInterface;
use Kaa\EventDispatcher\EventListenerInterface;
use Kaa\HttpKernel\Event\ResponseEvent;

abstract class AbstractResponseEventListener implements EventListenerInterface
{
    public function handle(EventInterface $event): void
    {
        $responseEvent = instance_cast($event, ResponseEvent::class);
        if ($responseEvent === null) {
            return;
        }

        $this->handleResponse($responseEvent);
    }

    abstract protected function handleResponse(ResponseEvent $event): void;
}
