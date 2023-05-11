<?php

namespace Kaa\HttpKernel\Event;

use Kaa\HttpFoundation\Request;
use Kaa\HttpKernel\HttpKernelInterface;
use Kaa\HttpFoundation\Response;

class ResponseEvent extends KernelEvent
{
    private Response $response;

    public function __construct(HttpKernelInterface $kernel, Request $request, int $requestType, Response $response)
    {
        parent::__construct($kernel, $request, $requestType);
        $this->setResponse($response);
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
