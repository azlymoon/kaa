<?php

namespace Kaa\HttpKernel\Event;

use Kaa\EventDispatcher\Event;
use Kaa\HttpKernel\Request;
use Kaa\HttpKernel\Response\ResponseInterface;

class RequestEvent extends Event
{
    private Request $request;

    private ?ResponseInterface $response = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
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
