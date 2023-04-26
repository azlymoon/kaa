<?php

namespace Kaa\HttpKernel\Event;

use Kaa\HttpFoundation\Request;
use Kaa\HttpFoundation\Response;
use Kaa\HttpKernel\HttpKernelInterface;

/**
 * Allows to filter a Response object.
 *
 * You can call getResponse() to retrieve the current response. With
 * setResponse() you can set a new response that will be returned to the
 * browser.
 */
final class ResponseEvent extends KernelEvent
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
