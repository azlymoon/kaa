<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class NamedServiceCollection
{
    /** @var ServiceCollection[] */
    private array $serviceCollections = [];

    public function add(string $alias, ServiceDefinition $service): self
    {
        if (!isset($this->serviceCollections[$alias])) {
            $this->serviceCollections[$alias] = new ServiceCollection();
        }

        $this->serviceCollections[$alias]->add($service);

        return $this;
    }

    public function has(string $alias): bool
    {
        return isset($this->serviceCollections[$alias]);
    }

    public function hasMany(string $alias): bool
    {
        return isset($this->serviceCollections[$alias]) && $this->serviceCollections[$alias]->hasMany();
    }

    /**
     * @return ServiceDefinition[]
     */
    public function getAll(string $alias): array
    {
        return $this->serviceCollections[$alias]->getAll();
    }

    public function getOne(string $alias): ServiceDefinition
    {
        return $this->serviceCollections[$alias]->get();
    }
}
