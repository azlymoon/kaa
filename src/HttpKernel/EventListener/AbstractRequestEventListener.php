<?php

namespace Kaa\HttpKernel\EventListener;

use Kaa\EventDispatcher\EventInterface;
use Kaa\EventDispatcher\EventListenerInterface;
use Kaa\HttpKernel\Event\RequestEvent;

abstract class AbstractRequestEventListener implements EventListenerInterface
{
    public function handle(EventInterface $event): void
    {
        $requestEvent = instance_cast($event, RequestEvent::class);
        if ($requestEvent === null) {
            return;
        }

        $this->handleRequest($requestEvent);
    }

    abstract protected function handleRequest(RequestEvent $event): void;
}
