<?php

namespace Kaa\HttpFoundation\Response;

interface ResponseInterface
{
    /**
     * Sends content and Cookies to the client
     */
    public function send(): void;
}
