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
     * @var string[] $attributes
     */
    private array $attributes;

    /**
     * @param string[] $attributes
     * @throws InvalidArgumentException
     */
    public function __construct(?string $username, ?string $password, array $attributes = [], bool $enabled = true)
    {
        if ('' === $username || null === $username) {
            throw new InvalidArgumentException('The username cannot be empty.');
        }
        $this->username = $username;
        $this->password = $password;
        $this->enabled = $enabled;
        $this->attributes = $attributes;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getRoles(): array
    {
        /**
         * @var string[] $roles
         */
        $roles = [];

        foreach ($this->attributes as $attribute) {
            if (str_starts_with($attribute, 'ROLE_')) {
                $roles[] = $attribute;
            }
        }
        return $roles;
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
     * @param string[] $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function addAttribute(string $newAttribute): void
    {
        if (in_array($newAttribute, $this->attributes, true)) {
            return;
        }
        $this->attributes[] = $newAttribute;
    }

    public function unsetAttribute(string $attributeToUnset): void
    {
        unset($this->attributes, $attributeToUnset);
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
