<?php

namespace Kaa\Security\Event;

use Kaa\HttpFoundation\Request;
use Kaa\HttpFoundation\Response;
use Kaa\Security\Token\TokenInterface;
use Kaa\Security\User\UserInterface;
use Kaa\Security\Authenticator\AuthenticatorInterface;
use Kaa\Security\Authenticator\Passport\Passport;
use Kaa\EventDispatcher\Event;

class LoginSuccessEvent extends Event
{
    private AuthenticatorInterface $authenticator;
    private Passport $passport;
    private TokenInterface $authenticatedToken;
    private ?TokenInterface $previousToken;
    private Request $request;
    private ?Response $response;
    private string $firewallName;

    public function __construct(AuthenticatorInterface $authenticator, Passport $passport, TokenInterface $authenticatedToken, Request $request, ?Response $response, string $firewallName, TokenInterface $previousToken = null)
    {
        $this->authenticator = $authenticator;
        $this->passport = $passport;
        $this->authenticatedToken = $authenticatedToken;
        $this->previousToken = $previousToken;
        $this->request = $request;
        $this->response = $response;
        $this->firewallName = $firewallName;
    }

    public function getAuthenticator(): AuthenticatorInterface
    {
        return $this->authenticator;
    }

    public function getPassport(): Passport
    {
        return $this->passport;
    }

    public function getUser(): UserInterface
    {
        return $this->passport->getUser();
    }

    public function getAuthenticatedToken(): TokenInterface
    {
        return $this->authenticatedToken;
    }

    public function getPreviousToken(): ?TokenInterface
    {
        return $this->previousToken;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getFirewallName(): string
    {
        return $this->firewallName;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}