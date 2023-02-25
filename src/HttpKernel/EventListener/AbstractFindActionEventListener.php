<?php

namespace Kaa\HttpKernel\EventListener;

use Kaa\EventDispatcher\EventInterface;
use Kaa\EventDispatcher\EventListenerInterface;
use Kaa\HttpKernel\Event\FindActionEvent;

abstract class AbstractFindActionEventListener implements EventListenerInterface
{
    public function handle(EventInterface $event): void
    {
        $findActionEvent = instance_cast($event, FindActionEvent::class);
        if ($findActionEvent === null) {
            return;
        }

        $this->handleFindAction($findActionEvent);
    }

    abstract protected function handleFindAction(FindActionEvent $event): void;
}
