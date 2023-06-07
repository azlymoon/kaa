<?php

namespace Kaa\Security\Event;

final class AuthenticationEvents
{
    public const AUTHENTICATION_SUCCESS = 'security.authentication.success';
    public const CHECK_PASSPORT = 'security.passport.check';
    public const TOKEN_CREATED = 'security.token.created';
    public const LOGIN_SUCCESS = 'security.login.success';
    public const LOGIN_FAILURE = 'security.login.failure';
}