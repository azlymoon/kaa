<?php

namespace Kaa\HttpKernel\Event;

use Kaa\EventDispatcher\Event;
use Kaa\HttpKernel\Request;
use Kaa\HttpKernel\Response\ResponseInterface;

class FindActionEvent extends Event
{
    private Request $request;

    /**
     * @var (callable(Request): ResponseInterface)|null
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
     * @return callable(Request): ResponseInterface $action
     */
    public function getAction(): callable
    {
        return $this->action;
    }

    /**
     * @param callable(Request): ResponseInterface $action
     */
    public function setAction(callable $action): void
    {
        $this->action = $action;
    }
}
