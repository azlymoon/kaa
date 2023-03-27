<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class AliasedServiceCollection
{
    /** @var ServiceDefinition[] */
    private array $services = [];

    /** @var ServiceDefinition[][] */
    private array $environmentServices = [];

    public function __construct(?ServiceDefinition $serviceDefinition = null)
    {
        if ($serviceDefinition !== null) {
            $this->add($serviceDefinition);
        }
    }

    public function add(ServiceDefinition $service): self
    {
        if (in_array($service, $this->services, true)) {
            return $this;
        }

        $this->services[] = $service;

        foreach ($service->environments as $environment) {
            if (!isset($this->environmentServices[$environment])) {
                $this->environmentServices[$environment] = [];
            }

            $this->environmentServices[$environment][] = $service;
        }

        return $this;
    }

    /**
     * Является ли коллекция сервисов неразрешимой (определены два сервиса в одном и том же окружении)
     */
    public function isDubious(): bool
    {
        if (count($this->services) === 1) {
            return false;
        }

        foreach ($this->environmentServices as $environmentServices) {
            if (count($environmentServices) > 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ServiceDefinition[]
     */
    public function get(): array
    {
        $definitions = [];
        foreach ($this->environmentServices as $environmentName => $environmentServices) {
            $definitions[$environmentName] = $environmentServices[0];
        }

        return $definitions;
    }

    /**
     * @return ServiceDefinition[][]
     */
    public function getAll(): array
    {
        return $this->environmentServices;
    }
}
