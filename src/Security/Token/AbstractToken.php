<?php

declare(strict_types=1);

namespace Kaa\Security\Token;

use Kaa\Security\User\UserInterface;

abstract class AbstractToken implements TokenInterface
{
    private ?UserInterface $user = null;

    /**
     * @var string[] $attributes
     */
    private array $attributes = [];

    /**
     * @var string[] $roles
     */
    private array $roles = [];

    public function __construct(?UserInterface $user)
    {
        if ($user != null) {
            $this->user = $user;
            $this->attributes = $user->getAttributes();
            $this->roles = $user->getRoles();
        }
    }

    public function getUserIdentifier(): ?string
    {
        return ($this->user == null) ? null : $this->user->getIdentifier();
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
        $this->attributes = $user->getAttributes();
        $this->roles = $user->getRoles();
    }
}
