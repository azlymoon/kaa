<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection;

use ArrayIterator;
use IteratorAggregate;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Attribute\When;
use Kaa\DependencyInjection\Exception\BadDefinitionException;
use Kaa\DependencyInjection\Exception\DoubleServiceConfigurationException;
use Traversable;

#[PhpOnly]
class ServiceCollection implements IteratorAggregate
{
    /** @var AliasedServiceCollection[] Алиас => коллекция сервисов с этим алиасом */
    private array $serviceCollections = [];

    /** @var ServiceDefinition[][] Имя сервиса => [Имя окружения => сервис с этим именем для этого окружения] */
    private array $services = [];

    /** @var ServiceDefinition[] */
    private array $uniqueServices = [];

    /**
     * @param ServiceDefinition[] $services
     * @throws CodeGenException
     */
    public function __construct(array $services = [])
    {
        foreach ($services as $service) {
            $this->add($service);
        }
    }

    /**
     * @throws CodeGenException
     */
    private function add(ServiceDefinition $service): void
    {
        $this->uniqueServices[] = $service;

        if (!isset($this->services[$service->name])) {
            $this->services[$service->name] = [];
        }

        foreach ($service->environments as $environment) {
            if (isset($this->services[$service->name][$environment])) {
                BadDefinitionException::throw(
                    'Service %s for environment %s was configured two times as %s and as %s',
                    $service->name,
                    $environment,
                    $this->services[$service->name][$environment]->class,
                    $service->class
                );
            }

            $this->services[$service->name][$environment] = $service;
        }

        foreach ($service->aliases as $alias) {
            if (!isset($this->serviceCollections[$alias])) {
                $this->serviceCollections[$alias] = new AliasedServiceCollection();
            }

            $this->serviceCollections[$alias]->add($service);
        }
    }

    /**
     * @throws CodeGenException
     */
    public function merge(ServiceDefinition $service): self
    {
        if (!isset($this->services[$service->name])) {
            $this->add($service);

            return $this;
        }

        $intersectedEnvironments =
            array_intersect($service->environments, array_keys($this->services[$service->name]));

        if (empty($intersectedEnvironments)) {
            $this->add($service);

            return $this;
        }

        if ($intersectedEnvironments !== [When::DEFAULT_ENVIRONMENT]) {
            BadDefinitionException::throw(
                'Service %s was configured multiple times (probably with PHP attributes and using config)',
                $service->name,
            );
        }

        if (!$service->nameIsClass()) {
            BadDefinitionException::throw(
                'Service %s was configured multiple times (probably with PHP attributes and using config)',
                $service->name,
            );
        }

        $identicalService = $this->services[$service->name][When::DEFAULT_ENVIRONMENT];
        if ($identicalService->wasConfigured()) {
            BadDefinitionException::throw(
                'Service %s was configured multiple times (probably with PHP attributes and using config)',
                $service->name,
            );
        }

        $identicalService->copyFrom($service);

        return $this;
    }

    public function haveName(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * @param string $name
     * @return ServiceDefinition[] Название окружения => сервис для этого окружения
     */
    public function getByName(string $name): array
    {
        return $this->services[$name];
    }

    public function haveAlias(string $alias): bool
    {
        return isset($this->serviceCollections[$alias]);
    }

    public function isDubiousAlias(string $alias): bool
    {
        return $this->serviceCollections[$alias]->isDubious();
    }

    /**
     * @return ServiceDefinition[][] Название окружения => массив сервисов для этого окружения
     */
    public function getAllByAlias(string $alias): array
    {
        return $this->serviceCollections[$alias]->getAll();
    }

    /**
     * @return ServiceDefinition[] Название окружения => сервис для этого окружения
     */
    public function getByAlias(string $alias): array
    {
        return $this->serviceCollections[$alias]->get();
    }

    /**
     * @return Traversable<ServiceDefinition>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->uniqueServices);
    }
}
