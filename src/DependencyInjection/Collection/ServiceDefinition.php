<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\DependencyInjection\Attribute\When;

#[PhpOnly]
class ServiceDefinition
{
    /**
     * @param string[] $aliases
     * @param string[] $environments
     */
    public function __construct(
        public readonly string $class,
        public readonly string $name,
        public array $aliases,
        public DependencyCollection $dependencies,
        public array $environments,
        public FactoryCollection $factories,
        public bool $isSingleton,
    ) {
        if (empty($this->environments)) {
            $this->environments = [When::DEFAULT_ENVIRONMENT];
        }
    }

    public function wasConfigured(): bool
    {
        return !$this->nameIsClass()
            || $this->dependencies->hasInjectedDependencies
            || $this->factories->notEmpty()
            || !$this->isSingleton;
    }

    public function nameIsClass(): bool
    {
        return $this->name === $this->class;
    }

    public function hasFactories(): bool
    {
        return $this->factories->notEmpty();
    }

    public function copyFrom(self $other): self
    {
        $this->aliases = $other->aliases;
        $this->dependencies = $other->dependencies;
        $this->environments = $other->environments;
        $this->factories = $other->factories;
        $this->isSingleton = $other->isSingleton;

        return $this;
    }
}
