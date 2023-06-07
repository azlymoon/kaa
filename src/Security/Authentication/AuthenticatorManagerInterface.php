<?php

namespace Kaa\Security\Authentication;

use Kaa\HttpFoundation\Request;
use Kaa\HttpFoundation\Response;

interface AuthenticatorManagerInterface
{
    /**
     * Tries to authenticate the request and returns a response.
     */
    public function authenticateRequest(Request $request): ?Response;
}
