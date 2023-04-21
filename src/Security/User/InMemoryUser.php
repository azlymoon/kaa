<?php

declare(strict_types=1);

namespace Kaa\Security\User;

use Kaa\Security\Exception\InvalidArgumentException;

class InMemoryUser implements UserInterface
{
    private string $username;

    private ?string $password;

    private bool $enabled;

    /**
     * @var string[] $roles
     */
    private array $roles;

    /**
     * @param string[] $roles
     * @throws InvalidArgumentException
     */
    public function __construct(?string $username, ?string $password, array $roles = [], bool $enabled = true)
    {
        if ('' === $username || null === $username) {
            throw new InvalidArgumentException('The username cannot be empty.');
        }
        $this->username = $username;
        $this->password = $password;
        $this->enabled = $enabled;
        $this->roles = $roles;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getIdentifier(): string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function setRole(string $newRole): void
    {
        if (in_array($newRole, $this->roles, true)) {
            return;
        }
        $this->roles[] = $newRole;
    }

    public function unsetRole(string $roleToUnset): void
    {
        unset($this->roles, $roleToUnset);
    }

    public function setIdentifier(string $identifier): void
    {
        if ($this->username != $identifier) {
            $this->username = $identifier;
        };
    }

    public function setPassword(string $password): void
    {
        if ($this->password != $password) {
            $this->password = $password;
        }
    }

    public function setEnabled(): void
    {
        $this->enabled = true;
    }

    public function setDisabled(): void
    {
        $this->enabled = false;
    }
}
