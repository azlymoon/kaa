<?php

namespace Kaa\EventDispatcher;

interface EventDispatcherInterface
{
    /**
     * Dispatches $event object to every listener subscribed to $eventName
     * The listeners are called by descending priority
     */
    public function dispatch(EventInterface $event, string $eventName): self;
}
