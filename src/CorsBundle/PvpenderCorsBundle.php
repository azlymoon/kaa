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

    /**
     * @param Request $request
     * @param Response $response
     * @param string[] $headers
     */
    public static function setResponseHeaders(Request $request, Response $response, array $headers): void
    {
        if (!empty($headers['forced_allow_origin_value'])){
            $response->setHeaders('Access-Control-Allow-Origin', $headers['forced_allow_origin_value']);
        }
        if (!empty($headers['allow_credentials'])) {
            $response->setHeaders('Access-Control-Allow-Credentials', 'true');
        }
        if (!empty($headers['allow_methods'])) {
            $response->setHeaders('Access-Control-Allow-Methods', $headers['allow_methods']);
        }
        if (!empty($headers['allow_headers'])) {
            $response->setHeaders('Access-Control-Allow-Headers', $headers['allow_headers']);
        }
        if (!empty($headers['max_age'])) {
            $response->setHeaders('Access-Control-Max-Age', $headers['max_age']);
        }

        if (strpos(($headers['allow_methods'].='OPTIONS'), $request->method()) === false) {
            $response->setStatusCode(405);
        }

        /**
         * We have to allow the header in the case-set as we received it by the client.
         * Firefox f.e. sends the LINK method as "Link", and we have to allow it like this or the browser will deny the
         * request.
         */
        if (strpos($headers['allow_methods'], $request->method()) === false) {
            $headers['allow_methods'] .= ", {$request->method()}";
            $response->setHeaders('Access-Control-Allow-Methods', $headers['allow_methods']);
        }

    }
}
