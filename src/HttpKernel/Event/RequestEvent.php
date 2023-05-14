<?php

namespace Kaa\HttpKernel\Event;

use Kaa\HttpFoundation\Response;

class RequestEvent extends KernelEvent
{
    private ?Response $response = null;

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
