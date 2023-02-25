<?php

namespace Kaa\HttpKernel\EventListener;

use Kaa\HttpKernel\Event\ThrowableEvent;
use Kaa\HttpKernel\HttpCode;
use Kaa\HttpKernel\Response\Response;

class ThrowableEventListener extends AbstractThrowableEventListener
{
    protected function handleThrowable(ThrowableEvent $event): void
    {
        $response = new Response('Server error', HttpCode::HTTP_INTERNAL_SERVER_ERROR);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
