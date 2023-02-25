<?php

namespace Kaa\EventDispatcher;

interface EventListenerInterface
{
    public function handle(EventInterface $event): void;
}
