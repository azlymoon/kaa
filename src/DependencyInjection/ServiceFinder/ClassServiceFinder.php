<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\ServiceFinder;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Kaa\DependencyInjection\Attribute\Inject;
use Kaa\DependencyInjection\Attribute\Service;
use Kaa\DependencyInjection\Exception\InvalidDependenciesException;
use Kaa\DependencyInjection\Exception\MatchNamespaceException;
use Kaa\DependencyInjection\ServiceDefinition;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;

class ClassServiceFinder implements ServiceFinderInterface
{
    /** @var mixed[] */
    private array $userConfig = [];

    /**
     * @param mixed[] $userConfig
     * @return ServiceDefinition[]
     *
     * @throws Exception
     */
    public function findServices(array $userConfig): array
    {
        $this->userConfig = $userConfig;

        ClassFinder::enablePSR4Vendors();

        $foundClasses = [];
        foreach ($this->userConfig['service']['namespaces'] ?? [] as $namespace) {
            $foundClasses[] = ClassFinder::getClassesInNamespace($namespace, ClassFinder::RECURSIVE_MODE);
        }

        $reflectionClasses = array_merge(...$foundClasses);
        $reflectionClasses = array_map(
            static fn(string $className) => new ReflectionClass($className),
            $reflectionClasses
        );
        $reflectionClasses = array_filter($reflectionClasses, $this->isNotInExcludedNamespace(...));
        $reflectionClasses = array_filter(
            $reflectionClasses,
            static fn(ReflectionClass $class) => $class->isInstantiable()
        );

        return array_map($this->createServiceDefinition(...), $reflectionClasses);
    }

    /**
     * @throws MatchNamespaceException
     */
    private function isNotInExcludedNamespace(ReflectionClass $reflectionClass): bool
    {
        $namespace = $reflectionClass->getNamespaceName();

        foreach ($this->userConfig['service']['exclude'] ?? [] as $excludedNamespace) {
            $excludedNamespace = ltrim($excludedNamespace, '\\');
            if (str_contains($excludedNamespace, '*')) {
                // * в паттерне заменяется на ровно одно вложенное пространство имён
                $excludedNamespaceRegexp = str_replace('*', '[^\\]+', $excludedNamespace);
                if (preg_match("/$excludedNamespaceRegexp/", $namespace, $matches) === false) {
                    throw new MatchNamespaceException(
                        sprintf(
                            'Error while matching namespace %s against regexp %s for exclusion defined as %s',
                            $namespace,
                            $excludedNamespaceRegexp,
                            $excludedNamespace,
                        )
                    );
                }

                if (!empty($matches)) {
                    return false;
                }

                continue;
            }

            if (str_starts_with($namespace, $excludedNamespace)) {
                return false;
            }
        }

        return true;
    }

    private function createServiceDefinition(ReflectionClass $serviceClass): ServiceDefinition
    {
        $serviceReflectionAttributes = $serviceClass->getAttributes(Service::class);
        /** @var Service[] $serviceAttributes */
        $serviceAttributes = array_map(
            static fn(ReflectionAttribute $attr) => $attr->newInstance(),
            $serviceReflectionAttributes
        );

        $aliases = $this->getServiceAliases($serviceClass, $serviceAttributes);
        $dependencies = $this->getServiceDependencies($serviceClass);

        return new ServiceDefinition($serviceClass->name, $aliases, $dependencies);
    }

    /**
     * @param Service[] $serviceAttributes
     * @return string[]
     */
    private function getServiceAliases(ReflectionClass $serviceClass, array $serviceAttributes): array
    {
        $aliases = [];
        foreach ($serviceAttributes as $serviceAttribute) {
            $aliases[] = is_string($serviceAttribute->aliases)
                ? [$serviceAttribute->aliases]
                : $serviceAttribute->aliases;
        }

        $aliases = array_merge(...$aliases);
        return [...$aliases, ...$this->getClassParents($serviceClass)];
    }

    /**
     * @return string[]
     */
    private function getClassParents(ReflectionClass $reflectionClass): array
    {
        $interfaces = [[$reflectionClass->name]];
        if ($reflectionClass->getParentClass() !== false) {
            $interfaces[] = $this->getClassParents($reflectionClass->getParentClass());
        }

        foreach ($reflectionClass->getInterfaces() as $interface) {
            $interfaces[] = $this->getClassParents($interface);
        }

        return array_merge(...$interfaces);
    }

    /**
     * @param ReflectionClass $serviceClass
     * @return string[] service names in the same order as they are accepted in the constructor
     * @throws InvalidDependenciesException
     */
    private function getServiceDependencies(ReflectionClass $serviceClass): array
    {
        $constructor = $serviceClass->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $injectReflectionAttributes = $parameter->getAttributes(Inject::class);
            if (empty($injectReflectionAttributes)) {
                $type = $parameter->getType();

                if (!$type instanceof ReflectionNamedType) {
                    throw new InvalidDependenciesException(
                        sprintf(
                            'Service %s must require service %s only with named type, not a union or an intersection',
                            $serviceClass->name,
                            $parameter->name
                        )
                    );
                }
                $dependencies[] = $type->getName();
                continue;
            }

            /** @var Inject $injectAttribute */
            $injectAttribute = $injectReflectionAttributes[0]->newInstance();
            $dependencies[] = $injectAttribute->service;
        }

        return $dependencies;
    }
}
