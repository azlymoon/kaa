<?php

namespace Kaa\Security\Event;

use Kaa\HttpFoundation\Request;
use Kaa\HttpFoundation\Response;
use Kaa\Security\Exception\AuthenticationException;
use Kaa\Security\Authenticator\AuthenticatorInterface;
use Kaa\Security\Authenticator\Passport\Passport;
use Kaa\EventDispatcher\Event;

class LoginFailureEvent extends Event
{
    private AuthenticationException $exception;
    private AuthenticatorInterface $authenticator;
    private Request $request;
    private ?Response $response;
    private string $firewallName;
    private ?Passport $passport;

    public function __construct(AuthenticationException $exception, AuthenticatorInterface $authenticator, Request $request, ?Response $response, string $firewallName, Passport $passport = null)
    {
        $this->exception = $exception;
        $this->authenticator = $authenticator;
        $this->request = $request;
        $this->response = $response;
        $this->firewallName = $firewallName;
        $this->passport = $passport;
    }

    public function getException(): AuthenticationException
    {
        return $this->exception;
    }

    public function getAuthenticator(): AuthenticatorInterface
    {
        return $this->authenticator;
    }

    public function getFirewallName(): string
    {
        return $this->firewallName;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setResponse(?Response $response)
    {
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getPassport(): ?Passport
    {
        return $this->passport;
    }
}