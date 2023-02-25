<?php

namespace Kaa\EventDispatcher;

class EventDispatcher implements ConfigurableEventDispatcherInterface
{
    /**
     * @var shape(listener: EventListenerInterface, priority: int)[][]
     */
    private array $listeners = [];

    /**
     * @var EventListenerInterface[][]
     */
    private array $sortedListeners = [];

    public function dispatch(EventInterface $event, string $eventName): self
    {
        if (empty($this->listeners[$eventName])) {
            return $this;
        }

        if (empty($this->sortedListeners[$eventName])) {
            $this->sortListeners($eventName);
        }

        $this->executeListeners($event, $eventName);

        return $this;
    }

    private function sortListeners(string $eventName): void
    {
        usort(
            $this->listeners[$eventName],
            static fn($first, $second) => -($first['priority'] <=> $second['priority'])
        );

        $this->sortedListeners[$eventName] = array_map(
            static fn($listenerShape) => $listenerShape['listener'],
            $this->listeners[$eventName]
        );
    }

    private function executeListeners(EventInterface $event, string $eventName): void
    {
        foreach ($this->sortedListeners[$eventName] as $listener) {
            if ($event->isPropagationStopped()) {
                return;
            }

            $listener->handle($event);
        }
    }

    public function addListener(string $eventName, EventListenerInterface $eventListener, int $priority = 0): self
    {
        $this->listeners[$eventName][] = shape(['listener' => $eventListener, 'priority' => $priority]);
        unset($this->sortedListeners[$eventName]);

        return $this;
    }

    public function removeListener(string $eventName, EventListenerInterface $eventListener): void
    {
        foreach ($this->listeners[$eventName] as $index => $listener) {
            if ($listener['listener'] === $eventListener) {
                unset($this->listeners[$eventName][$index], $this->sortedListeners[$eventName]);

                return;
            }
        }
    }

    public function hasListeners(?string $eventName = null): bool
    {
        if ($eventName !== null) {
            return !empty($this->listeners[$eventName]);
        }

        foreach ($this->listeners as $eventListeners) {
            if (!empty($eventListeners)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return EventListenerInterface[]
     */
    public function getListeners(string $eventName): array
    {
        if (empty($this->listeners[$eventName])) {
            return [];
        }

        if (empty($this->sortedListeners[$eventName])) {
            $this->sortListeners($eventName);
        }

        return $this->sortedListeners[$eventName];
    }

    public function getListenerPriority(string $eventName, EventListenerInterface $listener): ?int
    {
        if (empty($this->listeners[$eventName])) {
            return null;
        }

        foreach ($this->listeners[$eventName] as $listenerShape) {
            if ($listenerShape['listener'] === $listener) {
                return $listenerShape['priority'];
            }
        }

        return null;
    }
}
