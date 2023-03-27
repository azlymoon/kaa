<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection;

use ArrayIterator;
use IteratorAggregate;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Exception\BadDefinitionException;
use Kaa\DependencyInjection\Exception\DependencyNotFoundException;
use Traversable;

#[PhpOnly]
class DependencyCollection implements IteratorAggregate
{
    /**
     * @param Dependency[] $dependencies
     */
    public function __construct(
        public readonly array $dependencies = [],
        public bool $hasInjectedDependencies = false,
    ) {
    }

    public function have(string $varName): bool
    {
        foreach ($this->dependencies as $dependency) {
            if ($dependency->name === $varName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws CodeGenException
     */
    public function get(string $varName): Dependency
    {
        foreach ($this->dependencies as $dependency) {
            if ($dependency->name === $varName) {
                return $dependency;
            }
        }

        BadDefinitionException::throw('Dependency %s not found', $varName);
    }

    /**
     * @return Traversable<Dependency>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->dependencies);
    }
}
