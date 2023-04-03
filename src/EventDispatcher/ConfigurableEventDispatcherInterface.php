<?php

namespace Kaa\EventDispatcher;

interface ConfigurableEventDispatcherInterface extends EventDispatcherInterface
{
    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param callable(EventInterface): void $eventListener
     * @param int $priority The higher this value, the earlier an event listener will be triggered (defaults to 0)
     */
    public function addListener(string $eventName, callable $eventListener, int $priority = 0): self;

    /**
     * @param callable(EventInterface): void $eventListener
     * Removes subscription to $eventName for $eventListener
     */
    public function removeListener(string $eventName, callable $eventListener): void;

    /**
     * Return does this event dispatcher have any listeners if $eventName is null
     * Returns are there listeners subscribed to specific $eventName otherwise
     */
    public function hasListeners(?string $eventName = null): bool;

    /**
     * Returns array of listeners subscribed to $eventName, sorted in the order they will be called
     * @return (callable(EventInterface): void)[]
     */
    public function getListeners(string $eventName): array;

    /**
     * @param callable(EventInterface): void $listener
     * Returns priority of the lister or null if the listener is not subscribed to the event
     */
    public function getListenerPriority(string $eventName, callable $listener): ?int;
}
