<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\EventListenerTest\TestApp;

use Kaa\EventDispatcher\Event;

class MyEvent extends Event
{
    public const A = 'A';
    public const B = 'B';
    public const C = 'C';
}
