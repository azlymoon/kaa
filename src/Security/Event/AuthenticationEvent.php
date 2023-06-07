<?php

namespace Kaa\Security\Event;

use Kaa\Security\Token\TokenInterface;
use Kaa\EventDispatcher\Event;

/**
 * This is a general purpose authentication event.
 */
class AuthenticationEvent extends Event
{
    private TokenInterface $authenticationToken;

    public function __construct(TokenInterface $token)
    {
        $this->authenticationToken = $token;
    }

    public function getAuthenticationToken()
    {
        return $this->authenticationToken;
    }
}