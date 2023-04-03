<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\EventListenerTest\TestApp;

use Kaa\DependencyInjection\Attribute\AsEventListener;
use Kaa\EventDispatcher\EventInterface;

#[AsEventListener(MyEvent::A)]
#[AsEventListener(MyEvent::B, method: 'onB')]
#[AsEventListener(MyEvent::C, method: 'onC')]
class EventListener
{
    public bool $onACalled = false;
    public bool $onBCalled = false;
    public bool $onCCalled = false;

    public function __invoke(MyEvent $event): void
    {
        $this->onACalled = true;
    }

    public function onB(MyEvent $event): void
    {
        $this->onBCalled = true;
    }

    public function onC(EventInterface $event): void
    {
        $this->onCCalled = true;
    }
}
