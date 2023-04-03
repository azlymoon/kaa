<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Contract;

use Kaa\CodeGen\Contract\ServiceInfo;
use Kaa\CodeGen\Contract\ServiceStorageInterface;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Attribute\When;
use Kaa\DependencyInjection\Collection\Container;
use Kaa\DependencyInjection\Collection\Dependency\DependencyCollection;
use Kaa\DependencyInjection\Collection\FactoryCollection;
use Kaa\DependencyInjection\Collection\Service\ServiceDefinition;
use Kaa\DependencyInjection\Validator\ContainerValidator;
use ReflectionException;

readonly class ServiceStorage implements ServiceStorageInterface
{
    public function __construct(
        private Container $container,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws CodeGenException
     */
    public function addService(ServiceInfo $serviceInfo): void
    {
        $serviceDefinition = new ServiceDefinition(
            class: $serviceInfo->class,
            name: $serviceInfo->class,
            aliases: [],
            dependencies: new DependencyCollection(),
            environments: [When::DEFAULT_ENVIRONMENT],
            factories: new FactoryCollection(),
            isSingleton: true,
            tags: $serviceInfo->tags
        );

        $this->container->services->merge($serviceDefinition);
        (new ContainerValidator())->validate($this->container);
    }
}
