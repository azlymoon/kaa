<?php

declare(strict_types=1);

namespace Kaa\Security\User;

interface UserInterface
{
    /**
     * @return string[]
     */
    public function getRoles(): array;

    public function getIdentifier(): string;
}
