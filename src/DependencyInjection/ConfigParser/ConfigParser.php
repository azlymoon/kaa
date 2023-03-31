<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\ConfigParser;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Attribute\Factory;
use Kaa\DependencyInjection\Attribute\When;
use Kaa\DependencyInjection\Collection\Container;
use Kaa\DependencyInjection\Collection\Dependency\DependencyCollection;
use Kaa\DependencyInjection\Collection\FactoryCollection;
use Kaa\DependencyInjection\Collection\Parameter\Parameter;
use Kaa\DependencyInjection\Collection\Parameter\ParameterCollection;
use Kaa\DependencyInjection\Collection\Service\ServiceCollection;
use Kaa\DependencyInjection\Collection\Service\ServiceDefinition;
use Kaa\DependencyInjection\Exception\BadDefinitionException;
use Kaa\DependencyInjection\ReflectionUtils;
use ReflectionClass;
use ReflectionException;

#[PhpOnly]
class ConfigParser implements ConfigParserInterface
{
    private const IGNORED_KEYS = ['namespaces', 'exclude', 'parameters'];

    /**
     * @param mixed[] $userConfig
     * @throws BadDefinitionException
     * @throws CodeGenException
     * @throws ReflectionException
     */
    public function parseConfig(array $userConfig, ServiceCollection $services): Container
    {
        $this->parseServices($userConfig, $services);
        $parameters = $this->parseParameters($userConfig);

        return new Container($services, $parameters);
    }

    /**
     * @param mixed[] $userConfig
     * @throws CodeGenException
     * @throws BadDefinitionException
     * @throws ReflectionException
     */
    private function parseServices(array $userConfig, ServiceCollection $serviceCollection): void
    {
        foreach ($userConfig['service'] ?? [] as $serviceName => $definitionArray) {
            if (in_array($serviceName, self::IGNORED_KEYS, true)) {
                continue;
            }

            if (!is_array($definitionArray)) {
                BadDefinitionException::throw(
                    'Definition for service %s must be an array',
                    $serviceName
                );
            }

            $service = $this->parseService($serviceName, $definitionArray);
            $serviceCollection->merge($service);
        }
    }

    /**
     * @param mixed[] $definitionArray
     * @throws CodeGenException
     * @throws ReflectionException
     */
    private function parseService(
        string $serviceName,
        array $definitionArray,
    ): ServiceDefinition {
        if (class_exists($serviceName)) {
            $serviceClass = $serviceName;
            $serviceName = $definitionArray['name'] ?? $serviceClass;
        } else {
            $serviceClass = $definitionArray['class'] ?? BadDefinitionException::throw(
                'Service name %s is not an existing php class and there is no key "class" in it`s definition',
                $serviceName
            );

            if (!class_exists($serviceClass)) {
                BadDefinitionException::throw(
                    'Service %s defines it`s class as "%s", but the class does not exits',
                    $serviceName,
                    $serviceClass
                );
            }
        }

        $environments = (array)($definitionArray['when'] ?? []);

        if (
            array_key_exists('factories', $definitionArray)
            && array_key_exists('dependencies', $definitionArray)
        ) {
            BadDefinitionException::throw(
                'Service %s must not define both "dependencies" and "factories"',
                $serviceName
            );
        }

        $factories = new FactoryCollection($this->parseFactories($serviceName, $definitionArray));

        $reflectionClass = new ReflectionClass($serviceClass);
        if (!$reflectionClass->isInstantiable() && $factories->empty()) {
            BadDefinitionException::throw(
                'Service %s is not instantiable (is abstract class or interface) and does not define "factories"',
                $serviceName
            );
        }

        $dependencies = $factories->notEmpty()
            ? new DependencyCollection()
            : $this->parseDependencies($serviceName, $reflectionClass, $definitionArray);

        $serviceAliases = ReflectionUtils::getClassParents($reflectionClass);

        $singleton = $definitionArray['singleton'] ?? true;
        $tags = $definitionArray['tags'] ?? [];

        foreach ($tags['events'] ?? [] as &$event) {
            if (!array_key_exists('event', $event)) {
                BadDefinitionException::throw(
                    'Service %s defines "events", but does not define event name',
                    $serviceName
                );
            }

            $event['dispatcher'] ??= 'kernel.dispatcher';
            $event['priority'] ??= 0;
            $event['method'] ??= '__invoke';
        }

        return new ServiceDefinition(
            $serviceClass,
            $serviceName,
            $serviceAliases,
            $dependencies,
            $environments,
            $factories,
            $singleton,
            $tags
        );
    }

    /**
     * @param mixed[] $definitionArray
     * @return Factory[]
     * @throws CodeGenException
     */
    private function parseFactories(string $serviceName, array $definitionArray): array
    {
        $factories = [];
        foreach ($definitionArray['factories'] ?? [] as $factoryDefinition) {
            if (!array_key_exists('factory', $factoryDefinition)) {
                BadDefinitionException::throw(
                    'Service %s defines factories, but "factory" key is missing (value must be class or service name)',
                    $serviceName
                );
            }

            $factories[] = new Factory(
                $factoryDefinition['factory'],
                $factoryDefinition['method'] ?? '__invoke',
                $factoryDefinition['static'] ?? false,
                $factoryDefinition['when'] ?? [When::DEFAULT_ENVIRONMENT]
            );
        }

        return $factories;
    }

    /**
     * @param mixed[] $definitionArray
     * @throws CodeGenException
     */
    private function parseDependencies(
        string $serviceName,
        ReflectionClass $reflectionClass,
        array $definitionArray
    ): DependencyCollection {
        $dependencies = ReflectionUtils::getDependencies($reflectionClass);

        if (empty($definitionArray['dependencies'])) {
            return $dependencies;
        }

        $dependencies->hasInjectedDependencies = true;
        foreach ($definitionArray['dependencies'] as $name => $injectName) {
            assert(is_string($injectName));

            if (!$dependencies->have($name)) {
                BadDefinitionException::throw(
                    'Service %s defines dependency for parameter %s which is not present in it`s constructor',
                    $serviceName,
                    $name
                );
            }

            $dependencies->get($name)->injectedName = $injectName;
        }

        return $dependencies;
    }

    /**
     * @param mixed[] $userConfig
     * @throws CodeGenException
     */
    private function parseParameters(array $userConfig): ParameterCollection
    {
        $parameters = new ParameterCollection();
        foreach ($userConfig['service']['parameters'] ?? [] as $name => $definition) {
            if (!is_array($definition)) {
                BadDefinitionException::throw('Definition of parameter %s must be an array', $name);
            }

            if (!array_key_exists('value', $definition)) {
                BadDefinitionException::throw('Parameter %s must have "value" key defined', $name);
            }

            $parameters->add(
                new Parameter(
                    $name,
                    $definition['value'],
                    $definition['binding'] ?? null
                )
            );
        }

        return $parameters;
    }
}
