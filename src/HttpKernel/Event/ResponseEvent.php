<?php

namespace Kaa\HttpKernel\Event;

use Kaa\EventDispatcher\Event;
use Kaa\HttpFoundation\Request;
use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\HttpFoundation\Response;

class ResponseEvent extends Event
{
    private Request $request;

    private Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}
