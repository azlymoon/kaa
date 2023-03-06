<?php

namespace Kaa\HttpKernel\Event;

use Kaa\EventDispatcher\Event;
use Kaa\HttpFoundation\Request;
use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\HttpFoundation\Response;

class FindActionEvent extends Event
{
    private Request $request;

    /**
     * @var (callable(Request): Response)|null
     */
    private $action = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function hasAction(): bool
    {
        return $this->action !== null;
    }

    /**
     * @return callable(Request): Response $action
     */
    public function getAction(): callable
    {
        return $this->action;
    }

    /**
     * @param callable(Request): Response $action
     */
    public function setAction(callable $action): void
    {
        $this->action = $action;
    }
}
