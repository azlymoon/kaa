<b>Event Dispatcher </b>

This is a simple Event Dispatching/Handling library compatible with KPHP.

<b> Usage: </b>

```php
require_once '../vendor/autoload.php';

class MyEventListener implements \Kaa\EventDispatcher\EventListenerInterface
{
    public function handle(\Kaa\EventDispatcher\EventInterface $event): void
    {
        echo 'Event received';
        $event->stopPropagation();
    }
}

class MyEventListener2 implements \Kaa\EventDispatcher\EventListenerInterface
{
    public function handle(\Kaa\EventDispatcher\EventInterface $event): void
    {
        echo 'This code will not be executed';
        echo 'Event will not be received, because previous listener stopped propagation';
        $event->stopPropagation();
    }
}

$eventDispatcher = new \Kaa\EventDispatcher\EventDispatcher();

$eventDispatcher->addListener('test_event', new MyEventListener());
$eventDispatcher->addListener('test_event', new MyEventListener2(), -10);
$eventDispatcher->dispatch(new \Kaa\EventDispatcher\Event(), 'test_event');
```

<b> Reference </b>

EventDispatcher

`EventDispatcher::dispatch(EventInterface $event, string $eventName): self` - calls handle for all Handlers subscribed
to $eventName in decreasing priority order

`EventDispatcher::addListener(string $eventName, EventListenerInterface $eventListener, int $priority = 0): self` - Adds
listener for $eventName

`EventDispatcher::removeListener(string $eventName, EventListenerInterface $eventListener): void`

`EventDispatcher::public function hasListeners(?string $eventName = null): bool`

`EventDispatcher::getListeners(string $eventName): array`

`EventDispatcher::getListenerPriority(string $eventName, EventListenerInterface $listener): ?int`

`EventDispatcher::sortListeners(string $eventName): void`

EventInterface

`EventInterface::stopPropagation(): void` - if this method is called in one of the event listeners event will not be
passed to further listeners
