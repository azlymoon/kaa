<?php

namespace Kaa\Security\Token\Storage;

use Kaa\Security\Token\TokenInterface;

interface TokenStorageInterface
{
    public function getToken(): ?TokenInterface;

    public function setToken(?TokenInterface $token);
}