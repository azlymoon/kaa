<?php

declare(strict_types=1);

namespace Kaa\Security\Token;

use Kaa\Security\User\UserInterface;

interface TokenInterface
{
    public function getUserIdentifier(): ?string;

    /**
     * @return string[]
     */
    public function getRoles(): array;

    /**
     * @return string[]
     */
    public function getAttributes(): array;

    public function getUser(): ?UserInterface;

    public function setUser(UserInterface $user): void;
}
