<?php

namespace Kaa\HttpKernel\Event;

use Kaa\HttpFoundation\Response;

/**
 * Allows to create a response for a request.
 *
 * Call setResponse() to set the response that will be returned for the
 * current request. The propagation of this event is stopped as soon as a
 * response is set.
 *
 */
class RequestEvent extends KernelEvent
{
    private ?Response $response = null;

    /**
     * Returns the response object.
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Sets a response and stops event propagation.
     *
     * @param Response $response
     * @return void
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;

        $this->stopPropagation();
    }

    /**
     * Returns whether a response was set.
     */
    public function hasResponse(): bool
    {
        return null !== $this->response;
    }
}
