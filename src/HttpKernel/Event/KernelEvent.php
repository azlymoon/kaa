<?php

namespace Kaa\HttpKernel\Event;

use Kaa\HttpFoundation\Request;
use Kaa\HttpKernel\HttpKernelInterface;
use Kaa\EventDispatcher\Event;

/**
 * Base class for events thrown in the HttpKernel component.
 */
class KernelEvent extends Event
{
    private HttpKernelInterface $kernel;

    private Request $request;

    private int $requestType;

    /**
     * @param HttpKernelInterface $kernel
     * @param Request $request
     * @param int $requestType The request type the kernel is currently processing; one of
     *                         HttpKernelInterface::MAIN_REQUEST or HttpKernelInterface::SUB_REQUEST
     */
    public function __construct(HttpKernelInterface $kernel, Request $request, int $requestType)
    {
        $this->kernel = $kernel;
        $this->request = $request;
        $this->requestType = $requestType;
    }

    /**
     * Returns the kernel in which this event was thrown.
     */
    public function getKernel(): HttpKernelInterface
    {
        return $this->kernel;
    }

    /**
     * Returns the request the kernel is currently processing.
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Returns the request type the kernel is currently processing.
     *
     * @return int One of HttpKernelInterface::MAIN_REQUEST and
     *             HttpKernelInterface::SUB_REQUEST
     */
    public function getRequestType(): int
    {
        return $this->requestType;
    }

    /**
     * Checks if this is the main request.
     */
    public function isMainRequest(): bool
    {
        return HttpKernelInterface::MAIN_REQUEST === $this->requestType;
    }
}
