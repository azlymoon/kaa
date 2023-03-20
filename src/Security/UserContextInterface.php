<?php

declare(strict_types=1);

namespace Kaa\Security;

interface UserContextInterface
{
    /**
     * @return string[]
     */
    public function getRoles(): array;
}