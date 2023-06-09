<?php

namespace Kaa\CorsBundle\Event;

use Kaa\EventDispatcher\Event;
use Kaa\HttpFoundation\Request;
use Kaa\HttpFoundation\Response;

class OptionsEvent extends Event
{
    private Request $request;

    private ?Response $response = null;

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

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }
}