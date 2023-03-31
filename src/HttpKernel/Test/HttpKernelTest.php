<?php

namespace Kaa\HttpKernel\Test;

use Kaa\EventDispatcher\EventDispatcher;
use Kaa\EventDispatcher\EventListenerInterface;
use Kaa\HttpKernel\Event\FindActionEvent;
use Kaa\HttpKernel\Event\RequestEvent;
use Kaa\HttpKernel\Event\ResponseEvent;
use Kaa\HttpKernel\Event\ThrowableEvent;
use Kaa\HttpKernel\EventListener\AbstractFindActionEventListener;
use Kaa\HttpKernel\EventListener\AbstractRequestEventListener;
use Kaa\HttpKernel\EventListener\AbstractResponseEventListener;
use Kaa\HttpKernel\EventListener\AbstractThrowableEventListener;
use Kaa\HttpKernel\Exception\ResponseNotReachedException;
use Kaa\HttpKernel\HttpKernel;
use Kaa\HttpKernel\HttpKernelEvents;
use Kaa\HttpKernel\Request;
use Kaa\HttpKernel\Response\Response;
use PHPUnit\Framework\TestCase;
use RuntimeException;

trait ResponseAwareTrait
{
    public function __construct(
        private readonly Response $response,
    ) {
    }
}

class HttpKernelTest extends TestCase
{
    private ?HttpKernel $httpKernel;
    private ?EventDispatcher $dispatcher;

    /**
     * @throws ResponseNotReachedException
     */
    public function testRequestEventReturnsAResponse(): void
    {
        $response = new Response();
        $this->attachListenersToDispatcher([
            HttpKernelEvents::REQUEST => new ResponseReturningRequestEventListener($response),
        ]);

        $this->assertEquals($response, $this->httpKernel->handle(new Request()));
    }

    /**
     * @param EventListenerInterface[] $listeners
     */
    public function attachListenersToDispatcher(array $listeners): void
    {
        foreach ($listeners as $event => $listener) {
            $this->dispatcher->addListener($event, [$listener, 'handle']);
        }
    }

    /**
     * @throws ResponseNotReachedException
     */
    public function testResponseIsReturnedFromController(): void
    {
        $response = new Response();

        $this->attachListenersToDispatcher([
            HttpKernelEvents::FIND_ACTION => new FindActionEventListener($response),
        ]);

        $this->assertSame($response, $this->httpKernel->handle(new Request()));
    }

    /**
     * @throws ResponseNotReachedException
     */
    public function testResponseIsModifiedByResponseEventListener(): void
    {
        $response1 = new Response();
        $response2 = new Response();

        $this->attachListenersToDispatcher([
            HttpKernelEvents::FIND_ACTION => new FindActionEventListener($response1),
            HttpKernelEvents::RESPONSE => new ResponseEventListener($response2),
        ]);

        $actualResponse = $this->httpKernel->handle(new Request());

        $this->assertNotSame($response1, $actualResponse);
        $this->assertSame($response2, $actualResponse);
    }

    /**
     * @throws ResponseNotReachedException
     */
    public function testExceptionIsIntercepted(): void
    {
        $response1 = new Response();
        $response2 = new Response();

        $this->attachListenersToDispatcher([
            HttpKernelEvents::FIND_ACTION => new FindActionEventListener($response1),
            HttpKernelEvents::RESPONSE => new ThrowingResponseListener(),
            HttpKernelEvents::THROWABLE => new ThrowableEventListener($response2),
        ]);

        $actualResponse = $this->httpKernel->handle(new Request());

        $this->assertNotSame($response1, $actualResponse);
        $this->assertSame($response2, $actualResponse);
    }

    public function testResponseNotReachedIsThrows(): void
    {
        $this->expectException(ResponseNotReachedException::class);

        $this->httpKernel->handle(new Request());
    }

    protected function setUp(): void
    {
        $this->dispatcher = $this->createEventDispatcher();
        $this->httpKernel = $this->createHttpKernel($this->dispatcher);
    }

    public function createEventDispatcher(): EventDispatcher
    {
        return new EventDispatcher();
    }

    public function createHttpKernel(EventDispatcher $dispatcher): HttpKernel
    {
        return new HttpKernel($dispatcher);
    }

    protected function tearDown(): void
    {
        $this->httpKernel = null;
        $this->dispatcher = null;
    }
}

class ResponseReturningRequestEventListener extends AbstractRequestEventListener
{
    use ResponseAwareTrait;

    protected function handleRequest(RequestEvent $event): void
    {
        $event->setResponse($this->response);
        $event->stopPropagation();
    }
}

class FindActionEventListener extends AbstractFindActionEventListener
{
    use ResponseAwareTrait;

    protected function handleFindAction(FindActionEvent $event): void
    {
        $event->setAction(fn(Request $request) => $this->response);
        $event->stopPropagation();
    }
}

class ResponseEventListener extends AbstractResponseEventListener
{
    use ResponseAwareTrait;

    protected function handleResponse(ResponseEvent $event): void
    {
        $event->setResponse($this->response);
        $event->stopPropagation();
    }
}

class ThrowableEventListener extends AbstractThrowableEventListener
{
    use ResponseAwareTrait;

    protected function handleThrowable(ThrowableEvent $event): void
    {
        $event->setResponse($this->response);
    }
}

class ThrowingResponseListener extends AbstractResponseEventListener
{
    protected function handleResponse(ResponseEvent $event): void
    {
        throw new RuntimeException();
    }
}
