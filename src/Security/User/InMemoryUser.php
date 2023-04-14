<?php

declare(strict_types=1);

namespace Kaa\Security\User;

use Kaa\Security\Exception\InvalidArgumentException;

class InMemoryUser implements UserInterface
{
    /**
     * @var string $username
     */
    private string $username;

    /**
     * @var ?string $password
     */
    private ?string $password;

    /**
     * @var bool $enabled
     */
    private bool $enabled;

    /**
     * @var string[] $roles
     */
    private array $roles;

    /**
     * @param string|null $username
     * @param string|null $password
     * @param array $roles
     * @param bool $enabled
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

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return ?string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}