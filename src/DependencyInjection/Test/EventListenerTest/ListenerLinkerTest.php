<?php

/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\EventListenerTest;

use Kaa\CodeGen\Contract\BoostrapProviderInterface;
use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\GenerationManager;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\DependencyInjection\Test\EventListenerTest\TestApp\EventListener;
use Kaa\DependencyInjection\Test\EventListenerTest\TestApp\MyEvent;
use Kaa\DependencyInjection\Test\Utils;
use Kaa\EventDispatcher\EventDispatcher;
use PHPUnit\Framework\TestCase;

class ListenerLinkerTest extends TestCase
{
    private ?InstanceProvider $instanceProvider;

    public function setUp(): void
    {
        $configPath = __DIR__ . '/TestApp/config.php';
        $generationManager = new GenerationManager(require $configPath);

        $providedDependencies = $generationManager->generate();
        $this->instanceProvider = $providedDependencies->get(InstanceProviderInterface::class);

        $bootstrapCode = $providedDependencies->get(BoostrapProviderInterface::class)->getCallBootstrapCode();
        Utils::eval($bootstrapCode);
    }

    public function tearDown(): void
    {
        $this->instanceProvider = null;
    }

    public function testListenersWereCalled(): void
    {
        $dispatcherCode = $this->instanceProvider->provideInstanceCode('kernel.dispatcher');

        /** @var EventDispatcher $dispatcher */
        $dispatcher = Utils::eval($dispatcherCode);

        $dispatcher->dispatch(new MyEvent(), MyEvent::A);
        $dispatcher->dispatch(new MyEvent(), MyEvent::B);
        $dispatcher->dispatch(new MyEvent(), MyEvent::C);

        $listenerCode = $this->instanceProvider->provideInstanceCode(EventListener::class);

        /** @var EventListener $listener */
        $listener = Utils::eval($listenerCode);

        $this->assertTrue($listener->onACalled);
        $this->assertTrue($listener->onBCalled);
        $this->assertTrue($listener->onCCalled);
    }
}
