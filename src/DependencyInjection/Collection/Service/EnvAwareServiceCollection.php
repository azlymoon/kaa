<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection\Service;

use ArrayIterator;
use IteratorAggregate;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Attribute\When;
use Kaa\DependencyInjection\Exception\DependencyInjectionException;
use Kaa\DependencyInjection\ReflectionUtils;
use ReflectionException;
use Traversable;

class EnvAwareServiceCollection implements IteratorAggregate
{
    /** @var ServiceDefinition[] */
    private array $services = [];

    /** @var ServiceDefinition[][] - Например, ['prod' => [$def1, $def2], 'dev' => [$def3, $def4]] */
    private array $envToServices = [];

    /**
     * Если добавили сервис с именем, совпадающим с именем коллекции, то эта коллекция становится заблокированной
     * и содержит только этот сервис в окружении по умолчанию
     */
    private bool $isLocked = false;

    public function __construct(
        private readonly string $name
    ) {
    }

    public function add(ServiceDefinition $service): self
    {
        if ($this->isLocked) {
            return $this;
        }

        if ($service->class === $this->name) {
            $this->services = [$service];
            $this->envToServices = [When::DEFAULT_ENVIRONMENT => [$service]];
            $this->isLocked = true;

            return $this;
        }

        if (in_array($service, $this->services, true)) {
            return $this;
        }

        $this->services[] = $service;
        $this->addToEnvironments($service);

        return $this;
    }

    public function recalculateEnvironments(): self
    {
        if ($this->isLocked) {
            return $this;
        }

        $this->envToServices = [];

        foreach ($this->services as $service) {
            $this->addToEnvironments($service);
        }

        return $this;
    }

    private function addToEnvironments(ServiceDefinition $service): void
    {
        foreach ($service->environments as $environment) {
            if (!array_key_exists($environment, $this->envToServices)) {
                $this->envToServices[$environment] = [];
            }

            $this->envToServices[$environment][] = $service;
        }
    }

    /**
     * Является ли коллекция сервисов неразрешимой (определены два сервиса в одном и том же окружении)
     */
    public function areDubious(): bool
    {
        foreach ($this->envToServices as $environmentServices) {
            if (count($environmentServices) > 1) {
                return true;
            }
        }

        return false;
    }

    public function haveOnlyDefaultEnvironment(): bool
    {
        return array_keys($this->envToServices) === [When::DEFAULT_ENVIRONMENT];
    }

    /**
     * @return Traversable<ServiceDefinition>
     */
    public function getIterator(): Traversable
    {
        $definitions = [];
        foreach ($this->envToServices as $environmentName => $environmentServices) {
            $definitions[$environmentName] = $environmentServices[0];
        }

        return new ArrayIterator($definitions);
    }

    /**
     * @throws CodeGenException
     */
    public function getOnlyByEnvironment(string $environment): ServiceDefinition
    {
        if (!array_key_exists($environment, $this->envToServices)) {
            DependencyInjectionException::throw('Environment %s does not exist', $environment);
        }

        if (count($this->envToServices[$environment]) > 1) {
            DependencyInjectionException::throw(
                'There exist multiple implementations for environment %s',
                $environment
            );
        }

        return $this->envToServices[$environment][0];
    }

    /**
     * @throws ReflectionException
     */
    public function getCommonParent(): ?string
    {
        return ReflectionUtils::getCommonSuperClass(
            array_map(
                static fn(ServiceDefinition $service) => $service->class,
                $this->services,
            )
        );
    }

    /**
     * @return ServiceDefinition[][]
     */
    public function getAll(): array
    {
        return $this->envToServices;
    }
}
