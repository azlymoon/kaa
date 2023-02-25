<?php

namespace Kaa\EventDispatcher;

interface ConfigurableEventDispatcherInterface extends EventDispatcherInterface
{
    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param int $priority The higher this value, the earlier an event listener will be triggered (defaults to 0)
     */
    public function addListener(string $eventName, EventListenerInterface $eventListener, int $priority = 0): self;

    /**
     * Removes subscription to $eventName for $eventListener
     */
    public function removeListener(string $eventName, EventListenerInterface $eventListener): void;

    /**
     * Return does this event dispatcher have any listeners if $eventName is null
     * Returns are there listeners subscribed to specific $eventName otherwise
     */
    public function hasListeners(?string $eventName = null): bool;

    /**
     * Returns array of listeners subscribed to $eventName, sorted in the order they will be called
     * @return EventListenerInterface[]
     */
    public function getListeners(string $eventName): array;

    /**
     * Returns priority of the lister or null if the listener is not subscribed to the event
     */
    public function getListenerPriority(string $eventName, EventListenerInterface $listener): ?int;
}
