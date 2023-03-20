<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class ServiceCollection
{
    /** @var ServiceDefinition[] */
    private array $services = [];

    public function add(ServiceDefinition $serviceDefinition): self
    {
        $this->services[] = $serviceDefinition;

        return $this;
    }

    public function hasMany(): bool
    {
        return !$this->isSingle();
    }

    public function isSingle(): bool
    {
        return count($this->services) === 1;
    }

    public function get(): ServiceDefinition
    {
        return $this->services[0];
    }

    /**
     * @return ServiceDefinition[]
     */
    public function getAll(): array
    {
        return $this->services;
    }
}
