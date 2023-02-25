<?php

namespace Kaa\HttpKernel\Event;

use Kaa\EventDispatcher\Event;
use Kaa\HttpKernel\Request;
use Kaa\HttpKernel\Response\ResponseInterface;

class ResponseEvent extends Event
{
    private Request $request;

    private ResponseInterface $response;

    public function __construct(Request $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
