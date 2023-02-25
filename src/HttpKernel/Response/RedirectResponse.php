<?php

namespace Kaa\HttpKernel\Response;

class RedirectResponse implements ResponseInterface
{
    private string $url;

    public function __construct(string $redirectUrl)
    {
        $this->url = $redirectUrl;
    }

    public function send(): void
    {
        header('Location: ' . $this->url);
    }
}
