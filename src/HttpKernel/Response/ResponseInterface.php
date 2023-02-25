<?php

namespace Kaa\HttpKernel\Response;

interface ResponseInterface
{
    /**
     * Sends content and Cookies to the client
     */
    public function send(): void;
}
