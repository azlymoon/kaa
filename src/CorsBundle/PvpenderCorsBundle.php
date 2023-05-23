<?php

namespace Kaa\CorsBundle;

use Kaa\HttpKernel\Request;
use Kaa\HttpKernel\Response\Response;

class PvpenderCorsBundle
{
    public static function checkOptions(\Kaa\EventDispatcher\EventInterface $event): void
    {
        $castedEvent = instance_cast($event, \Kaa\HttpKernel\Event\FindActionEvent::class);
        if ($castedEvent === null) {
            return;
        }
        $method = $castedEvent->getRequest()->method();
        if ($method !== 'OPTIONS') {
            return;
        }
        $castedEvent->setAction([PvpenderCorsBundle::class, 'returnEmptyResponse']);
        $castedEvent->stopPropagation();
    }

    public static function returnEmptyResponse(Request $request): Response
    {
        return new Response();
    }
}
