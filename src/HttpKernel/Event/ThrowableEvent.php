<?php

namespace Kaa\HttpKernel\Event;

use Kaa\EventDispatcher\Event;
use Kaa\HttpKernel\Request;
use Kaa\HttpKernel\Response\ResponseInterface;
use Throwable;

class ThrowableEvent extends Event
{
    private Throwable $throwable;

    private Request $request;

    private ?ResponseInterface $response = null;

    public function __construct(Throwable $throwable, Request $request)
    {
        $this->throwable = $throwable;
        $this->request = $request;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(?ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
