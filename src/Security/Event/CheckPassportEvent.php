<?php

namespace Kaa\Security\Event;

use Kaa\Security\Authenticator\AuthenticatorInterface;
use Kaa\Security\Authenticator\Passport\Passport;
use Kaa\EventDispatcher\Event;

class CheckPassportEvent extends Event
{
    private AuthenticatorInterface $authenticator;
    private Passport $passport;

    public function __construct(AuthenticatorInterface $authenticator, Passport $passport)
    {
        $this->authenticator = $authenticator;
        $this->passport = $passport;
    }

    public function getAuthenticator(): AuthenticatorInterface
    {
        return $this->authenticator;
    }

    public function getPassport(): Passport
    {
        return $this->passport;
    }
}
