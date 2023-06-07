<?php

namespace Kaa\Security\Event;

use Kaa\Security\Token\TokenInterface;
use Kaa\Security\Authenticator\Passport\Passport;
use Kaa\EventDispatcher\Event;


class AuthenticationTokenCreatedEvent extends Event
{
    private TokenInterface $authenticatedToken;
    private Passport $passport;

    public function __construct(TokenInterface $token, Passport $passport)
    {
        $this->authenticatedToken = $token;
        $this->passport = $passport;
    }

    public function getAuthenticatedToken(): TokenInterface
    {
        return $this->authenticatedToken;
    }

    public function setAuthenticatedToken(TokenInterface $authenticatedToken): void
    {
        $this->authenticatedToken = $authenticatedToken;
    }

    public function getPassport(): Passport
    {
        return $this->passport;
    }
}