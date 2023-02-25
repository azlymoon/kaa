<?php

namespace Kaa\HttpKernel\EventListener;

use Kaa\EventDispatcher\EventInterface;
use Kaa\EventDispatcher\EventListenerInterface;
use Kaa\HttpKernel\Event\ThrowableEvent;

abstract class AbstractThrowableEventListener implements EventListenerInterface
{
    public function handle(EventInterface $event): void
    {
        $throwableEvent = instance_cast($event, ThrowableEvent::class);
        if ($throwableEvent === null) {
            return;
        }

        $this->handleThrowable($throwableEvent);
    }

    abstract protected function handleThrowable(ThrowableEvent $event): void;
}
