<?php

namespace Kaa\HttpFoundation\MyTests;

use Kaa\HttpFoundation\Request;

class RequestContentProxy extends Request
{
    public function getContent(bool $asResource = false): string
    {
        return http_build_query(['_method' => 'PUT', 'content' => 'mycontent'], '', '&');
    }
}