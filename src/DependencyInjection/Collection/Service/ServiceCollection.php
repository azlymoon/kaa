<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection\Service;

use ArrayIterator;
use IteratorAggregate;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Exception\DependencyNotFoundException;
use Traversable;

#[PhpOnly]
class ServiceCollection implements IteratorAggregate
{
    /** @var EnvAwareServiceCollection[] Имя сервиса => Коллекция сервисов с этим именем */
    private array $nameToServices = [];

    /** @var ServiceDefinition[] */
    private array $services = [];

    /** @var array<string, ServiceDefinition[]> */
    private array $tagNamesToServices = [];

    /**
     * @param ServiceDefinition[] $services
     */
    public function __construct(array $services = [])
    {
        foreach ($services as $service) {
            $this->add($service);
        }
    }

    private function add(ServiceDefinition $service): void
    {
        $this->services[] = $service;

        if (!array_key_exists($service->name, $this->nameToServices)) {
            $this->nameToServices[$service->name] = new EnvAwareServiceCollection($service->name);
        }

        $this->nameToServices[$service->name]->add($service);

        foreach ($service->aliases as $alias) {
            if (!array_key_exists($alias, $this->nameToServices)) {
                $this->nameToServices[$alias] = new EnvAwareServiceCollection($alias);
            }

            $this->nameToServices[$alias]->add($service);
        }

        foreach ($service->tags as $tagName => $_) {
            if (!array_key_exists($tagName, $this->tagNamesToServices)) {
                $this->tagNamesToServices[$tagName] = [];
            }

            $this->tagNamesToServices[$tagName][] = $service;
        }
    }

    public function merge(ServiceDefinition $service): self
    {
        $identicalServices = array_filter(
            $this->services,
            static fn (ServiceDefinition $s) => $s->name === $service->name && $s->class === $service->class
        );

        if (empty($identicalServices)) {
            $this->add($service);

            return $this;
        }

        $identicalService = reset($identicalServices);
        foreach ($identicalService->tags as $tag) {
            $serviceNameIndex = array_search($identicalService, $this->tagNamesToServices[$tag], true);
            unset($this->tagNamesToServices[$tag][$serviceNameIndex]);
        }

        $identicalService->copyFrom($service);

        $this->nameToServices[$identicalService->name]->recalculateEnvironments();
        foreach ($identicalService->aliases as $alias) {
            $this->nameToServices[$alias]->recalculateEnvironments();
        }

        foreach ($service->tags as $tagName => $_) {
            if (!array_key_exists($tagName, $this->tagNamesToServices)) {
                $this->tagNamesToServices[$tagName] = [];
            }

            $this->tagNamesToServices[$tagName][] = $identicalService;
        }

        return $this;
    }

    public function have(string $nameOrAlias): bool
    {
        return array_key_exists($nameOrAlias, $this->nameToServices);
    }

    /**
     * @throws CodeGenException
     */
    public function get(string $nameOrAlias): EnvAwareServiceCollection
    {
        if (!array_key_exists($nameOrAlias, $this->nameToServices)) {
            DependencyNotFoundException::throw('Service "%s" does not exist', $nameOrAlias);
        }

        return $this->nameToServices[$nameOrAlias];
    }

    /**
     * @return Traversable<ServiceDefinition>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->services);
    }

    /**
     * @return ServiceDefinition[]
     */
    public function getServicesByTagName(string $tagName): array
    {
        return $this->tagNamesToServices[$tagName] ?? [];
    }
}
