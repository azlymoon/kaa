<?php

namespace Kaa\EventDispatcher\Test;

use Kaa\EventDispatcher\Event;
use Kaa\EventDispatcher\EventDispatcher;
use Kaa\EventDispatcher\EventInterface;
use Kaa\EventDispatcher\EventListenerInterface;
use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    private const FIRST_EVENT = 'first.event';
    private const SECOND_EVENT = 'second.event';

    private ?EventDispatcher $dispatcher;

    public function testInitialStateHasNoListeners(): void
    {
        $this->assertEquals([], $this->dispatcher->getListeners(self::FIRST_EVENT));
        $this->assertEquals([], $this->dispatcher->getListeners(self::SECOND_EVENT));
        $this->assertFalse($this->dispatcher->hasListeners());
        $this->assertFalse($this->dispatcher->hasListeners(self::FIRST_EVENT));
        $this->assertFalse($this->dispatcher->hasListeners(self::SECOND_EVENT));
    }

    public function testAddListener(): void
    {
        $listener = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, $listener)
            ->addListener(self::SECOND_EVENT, $listener);

        $this->assertTrue($this->dispatcher->hasListeners());
        $this->assertTrue($this->dispatcher->hasListeners(self::FIRST_EVENT));
        $this->assertTrue($this->dispatcher->hasListeners(self::SECOND_EVENT));
        $this->assertCount(1, $this->dispatcher->getListeners(self::FIRST_EVENT));
        $this->assertCount(1, $this->dispatcher->getListeners(self::SECOND_EVENT));
    }

    private function createEventListener(string $dataString = '', bool $shallStopPropagation = false): TestEventListener
    {
        return new TestEventListener($dataString, $shallStopPropagation);
    }

    public function testGetListenersSortsByPriority(): void
    {
        $listener1 = $this->createEventListener();
        $listener2 = $this->createEventListener();
        $listener3 = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, $listener1, -10)
            ->addListener(self::FIRST_EVENT, $listener2, 10)
            ->addListener(self::FIRST_EVENT, $listener3);

        $expected = [
            $listener2,
            $listener3,
            $listener1,
        ];

        $this->assertSame($expected, $this->dispatcher->getListeners(self::FIRST_EVENT));
    }

    public function testGetListenerPriority(): void
    {
        $listener1 = $this->createEventListener();
        $listener2 = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, $listener1, -10)
            ->addListener(self::FIRST_EVENT, $listener2);

        $this->assertSame(-10, $this->dispatcher->getListenerPriority(self::FIRST_EVENT, $listener1));
        $this->assertSame(0, $this->dispatcher->getListenerPriority(self::FIRST_EVENT, $listener2));
        $this->assertNull($this->dispatcher->getListenerPriority(self::SECOND_EVENT, $listener2));
        $this->assertNull($this->dispatcher->getListenerPriority(self::FIRST_EVENT, $this->createEventListener()));
    }

    public function testDispatch(): void
    {
        $listener1 = $this->createEventListener();
        $listener2 = $this->createEventListener();
        $listener3 = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, $listener1)
            ->addListener(self::FIRST_EVENT, $listener2)
            ->addListener(self::SECOND_EVENT, $listener3);

        $this->dispatcher
            ->dispatch(new TestEvent(), self::FIRST_EVENT)
            ->dispatch(new TestEvent(), self::SECOND_EVENT);

        $this->assertTrue($listener1->wasInvoked);
        $this->assertTrue($listener2->wasInvoked);
        $this->assertTrue($listener3->wasInvoked);
    }

    public function testDispatchByPriority(): void
    {
        $listener1 = $this->createEventListener('1');
        $listener2 = $this->createEventListener('2');
        $listener3 = $this->createEventListener('3');

        $testEvent = new TestEvent();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, $listener1, -10)
            ->addListener(self::FIRST_EVENT, $listener2, 10)
            ->addListener(self::FIRST_EVENT, $listener3);

        $this->dispatcher->dispatch($testEvent, self::FIRST_EVENT);

        $this->assertEquals('231', $testEvent->dataString);
    }

    public function testStopPropagation(): void
    {
        $listener1 = $this->createEventListener(shallStopPropagation: true);
        $listener2 = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, $listener1, 10)
            ->addListener(self::FIRST_EVENT, $listener2);

        $this->dispatcher->dispatch(new TestEvent(), self::FIRST_EVENT);

        $this->assertTrue($listener1->wasInvoked);
        $this->assertFalse($listener2->wasInvoked);
    }

    public function testRemoveListeners(): void
    {
        $listener1 = $this->createEventListener();
        $listener2 = $this->createEventListener();
        $listener3 = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, $listener1)
            ->addListener(self::FIRST_EVENT, $listener2)
            ->addListener(self::SECOND_EVENT, $listener3);

        $this->assertCount(2, $this->dispatcher->getListeners(self::FIRST_EVENT));
        $this->assertCount(1, $this->dispatcher->getListeners(self::SECOND_EVENT));

        $this->dispatcher->removeListener(self::FIRST_EVENT, $listener1);

        $this->assertCount(1, $this->dispatcher->getListeners(self::FIRST_EVENT));
        $this->assertCount(1, $this->dispatcher->getListeners(self::SECOND_EVENT));

        $this->dispatcher->dispatch(new TestEvent(), self::FIRST_EVENT);

        $this->assertFalse($listener1->wasInvoked);
    }

    public function testRemoveListenerOnlyForOneEvent(): void
    {
        $listener = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, $listener)
            ->addListener(self::SECOND_EVENT, $listener);

        $this->assertTrue($this->dispatcher->hasListeners(self::FIRST_EVENT));
        $this->assertTrue($this->dispatcher->hasListeners(self::SECOND_EVENT));

        $this->dispatcher->removeListener(self::FIRST_EVENT, $listener);

        $this->assertFalse($this->dispatcher->hasListeners(self::FIRST_EVENT));
        $this->assertTrue($this->dispatcher->hasListeners(self::SECOND_EVENT));

        $this->dispatcher->dispatch(new TestEvent(), self::SECOND_EVENT);

        $this->assertTrue($listener->wasInvoked);
    }

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
    }

    protected function tearDown(): void
    {
        $this->dispatcher = null;
    }
}

class TestEvent extends Event
{
    public string $dataString = '';
}

class TestEventListener implements EventListenerInterface
{
    public bool $wasInvoked = false;

    public function __construct(
        private readonly string $dataString = '',
        private readonly bool $shallStopPropagation = false,
    ) {
    }

    public function handle(EventInterface $event): void
    {
        $testEvent = instance_cast($event, TestEvent::class);
        if ($testEvent === null) {
            return;
        }

        $testEvent->dataString .= $this->dataString;
        $this->wasInvoked = true;

        if ($this->shallStopPropagation) {
            $event->stopPropagation();
        }
    }
}
