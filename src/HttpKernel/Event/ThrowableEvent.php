<?php

namespace Kaa\HttpKernel\Event;

use Kaa\EventDispatcher\Event;
use Kaa\HttpFoundation\Request;
use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\HttpFoundation\Response;
use Throwable;

class ThrowableEvent extends Event
{
    private Throwable $throwable;

    private Request $request;

    private ?Response $response = null;

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

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }
}
