<?php

namespace Kaa\Security\Authentication;

use Kaa\HttpFoundation\Request;
use Kaa\HttpFoundation\Response;
use Kaa\Security\Token\Storage\TokenStorageInterface;
use Kaa\Security\Token\TokenInterface;
#use Kaa\Security\Authenticator\AuthenticatorInterface;
#use Kaa\Security\Authenticator\Passport\Badge\BadgeInterface;
#use Kaa\Security\Authenticator\Passport\Badge\UserBadge;
#use Kaa\Security\Authenticator\Passport\Passport;
use Kaa\Security\Event\AuthenticationEvents;
use Kaa\Security\Event\AuthenticationSuccessEvent;
use Kaa\Security\Event\AuthenticationTokenCreatedEvent;
use Kaa\Security\Event\CheckPassportEvent;
use Kaa\Security\Event\LoginFailureEvent;
use Kaa\Security\Event\LoginSuccessEvent;
use Kaa\Security\Exception\AuthenticationException;
use Kaa\Security\Exception\BadCredentialsException;
use Kaa\EventDispatcher\EventDispatcherInterface;


class AuthenticatorManager implements AuthenticatorManagerInterface
{
    private iterable $authenticators;
    private TokenStorageInterface $tokenStorage;
    private EventDispatcherInterface $eventDispatcher;
    private bool $eraseCredentials;
    private string $firewallName;
    private array $requiredBadges;

    /**
     * @param iterable<AuthenticatorInterface> $authenticators
     */
    public function __construct(iterable $authenticators, TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher, string $firewallName, bool $eraseCredentials=true, array $requiredBadges = [])
    {
        $this->authenticators = $authenticators;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->firewallName = $firewallName;
        $this->eraseCredentials = $eraseCredentials;
        $this->requiredBadges = $requiredBadges;
    }

    public function authenticateRequest(Request $request): ?Response
    {
        $authenticators = $request->attributes->get('_security_authenticators');
        if (!$authenticators) {
            return null;
        }
        return $this->executeAuthenticators($authenticators, $request);
    }

    /**
     * @param AuthenticatorInterface[] $authenticators
     */
    private function executeAuthenticators(array $authenticators, Request $request): ?Response
    {
        foreach ($authenticators as $authenticator) {
            $response = $this->executeAuthenticator($authenticator, $request);
            if (null !== $response) {
                return $response;
            }
        }
        return null;
    }

    private function executeAuthenticator(AuthenticatorInterface $authenticator, Request $request): ?Response
    {
        $passport = null;
        $previousToken = $this->tokenStorage->getToken();
        try {
            $passport = $authenticator->authenticate($request);
            $this->eventDispatcher->dispatch(new CheckPassportEvent($authenticator, $passport), AuthenticationEvents::CHECK_PASSPORT);

            $resolvedBadges = [];
            foreach ($passport->getBadges() as $badge) {
                if (!$badge->isResolved()) {
                    throw new BadCredentialsException('Authentication failed: Security badge is not resolved.');
                }
                $resolvedBadges[] = $badge::class;
            }

            $missingRequiredBadges = array_diff($this->requiredBadges, $resolvedBadges);
            if ($missingRequiredBadges) {
                throw new BadCredentialsException('Authentication failed: Some required badges are not available on the passport.');
            }

            $authenticatedToken = $authenticator->createToken($passport, $this->firewallName);

            $authenticatedToken = $this->eventDispatcher->dispatch(new AuthenticationTokenCreatedEvent($authenticatedToken, $passport), AuthenticationEvents::TOKEN_CREATED)->getAuthenticatedToken();

            if (true === $this->eraseCredentials) {
                $authenticatedToken->eraseCredentials();
            }

            $this->eventDispatcher->dispatch(new AuthenticationSuccessEvent($authenticatedToken), AuthenticationEvents::AUTHENTICATION_SUCCESS);

        } catch (AuthenticationException $e) {
            return $this->handleAuthenticationFailure($e, $request, $authenticator, $passport);
        }

        return $this->handleAuthenticationSuccess($authenticatedToken, $passport, $request, $authenticator, $previousToken);
    }

    private function handleAuthenticationSuccess(TokenInterface $authenticatedToken, Passport $passport, Request $request, AuthenticatorInterface $authenticator, ?TokenInterface $previousToken): ?Response
    {
        $this->tokenStorage->setToken($authenticatedToken);
        $response = $authenticator->onAuthenticationSuccess($request, $authenticatedToken, $this->firewallName);
        $this->eventDispatcher->dispatch($loginSuccessEvent = new LoginSuccessEvent($authenticator, $passport, $authenticatedToken, $request, $response, $this->firewallName, $previousToken), AuthenticationEvents::LOGIN_SUCCESS);
        return $loginSuccessEvent->getResponse();
    }

    private function handleAuthenticationFailure(AuthenticationException $authenticationException, Request $request, AuthenticatorInterface $authenticator, ?Passport $passport): ?Response
    {
        $response = $authenticator->onAuthenticationFailure($request, $authenticationException);
        $this->eventDispatcher->dispatch($loginFailureEvent = new LoginFailureEvent($authenticationException, $authenticator, $request, $response, $this->firewallName, $passport), AuthenticationEvents::LOGIN_FAILURE);
        return $loginFailureEvent->getResponse();
    }
}