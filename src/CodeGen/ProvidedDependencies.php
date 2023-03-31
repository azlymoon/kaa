<?php

declare(strict_types=1);

namespace Kaa\CodeGen;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\InvalidDependencyException;
use Kaa\CodeGen\Exception\NoDependencyException;
use T;

#[PhpOnly]
class ProvidedDependencies
{
    /**
     * @var array<string, object>
     */
    private array $providedDependencies = [];

    /**
     * @throws InvalidDependencyException
     */
    public function add(string $interface, object $implementation): self
    {
        if (!is_subclass_of($implementation, $interface)) {
            throw new InvalidDependencyException(
                sprintf(
                    "%s is not instance of %s. So it cannot be added to providedDependencies with key %s",
                    $implementation::class,
                    $interface,
                    $interface,
                )
            );
        }

        $this->providedDependencies[$interface] = $implementation;

        return $this;
    }

    public function has(string $interface): bool
    {
        return !empty($this->providedDependencies[$interface]);
    }

    /**
     * @template T
     * @param class-string<T> $interface
     * @return T
     * @throws NoDependencyException
     */
    public function get(string $interface, ?object $default = null): object
    {
        return $this->providedDependencies[$interface] ?? $default ?? throw new NoDependencyException();
    }
}
