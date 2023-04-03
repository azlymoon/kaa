<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\ServiceFinder;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Attribute\AsEventListener;
use Kaa\DependencyInjection\Attribute\Factory;
use Kaa\DependencyInjection\Attribute\Service;
use Kaa\DependencyInjection\Attribute\When;
use Kaa\DependencyInjection\Collection\Dependency\DependencyCollection;
use Kaa\DependencyInjection\Collection\FactoryCollection;
use Kaa\DependencyInjection\Collection\Service\ServiceDefinition;
use Kaa\DependencyInjection\Exception\MatchNamespaceException;
use Kaa\DependencyInjection\ReflectionUtils;
use ReflectionAttribute;
use ReflectionClass;

class ServiceFinder implements ServiceFinderInterface
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

        ClassFinder::disablePSR4Vendors();

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
                || !empty($class->getAttributes(Factory::class))
        );

        return array_map($this->createServiceDefinition(...), $reflectionClasses);
    }

    /**
     * @throws MatchNamespaceException
     * @throws CodeGenException
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
                    MatchNamespaceException::throw(
                        'Error while matching namespace %s against regexp %s for exclusion defined as %s',
                        $namespace,
                        $excludedNamespaceRegexp,
                        $excludedNamespace,
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

    /**
     * @throws CodeGenException
     */
    private function createServiceDefinition(ReflectionClass $serviceClass): ServiceDefinition
    {
        $serviceReflectionAttributes = $serviceClass->getAttributes(Service::class);
        /** @var Service[] $serviceAttributes */
        $serviceAttributes = array_map(
            static fn(ReflectionAttribute $attr) => $attr->newInstance(),
            $serviceReflectionAttributes
        );

        $singleton = true;
        $serviceName = $serviceClass->name;
        $tags = [];
        if (!empty($serviceAttributes) && $serviceAttributes[0]->name !== null) {
            $serviceName = $serviceAttributes[0]->name;
            $singleton = $serviceAttributes[0]->singleton;
            $tags = $serviceAttributes[0]->tags;
        }

        $aliases = ReflectionUtils::getClassParents($serviceClass);

        $whenReflectionAttributes = $serviceClass->getAttributes(When::class);

        /** @var string[][] $environments */
        $environments = array_map(
            static fn(ReflectionAttribute $attr) => (array) $attr->newInstance()->environment,
            $whenReflectionAttributes
        );

        /** @var string[] $environments */
        $environments = array_merge(...$environments);

        $factoryReflectionAttributes = $serviceClass->getAttributes(Factory::class);
        $factories = new FactoryCollection(
            array_map(
                static fn(ReflectionAttribute $attr) => $attr->newInstance(),
                $factoryReflectionAttributes
            )
        );

        $dependencies = $factories->notEmpty()
            ? new DependencyCollection()
            : ReflectionUtils::getDependencies($serviceClass);

        /** @var AsEventListener[] $asEventListenerAttributes */
        $asEventListenerAttributes = array_map(
            static fn(ReflectionAttribute $attr) => $attr->newInstance(),
            $serviceClass->getAttributes(AsEventListener::class),
        );

        foreach ($asEventListenerAttributes as $asEventListenerAttribute) {
            if (!array_key_exists('events', $tags)) {
                $tags['events'] = [];
            }

            $tags['events'][] = [
                'event' => $asEventListenerAttribute->event,
                'dispatcher' => $asEventListenerAttribute->dispatcher,
                'priority' => $asEventListenerAttribute->priority,
                'method' => $asEventListenerAttribute->method,
            ];
        }

        return new ServiceDefinition(
            $serviceClass->name,
            $serviceName,
            $aliases,
            $dependencies,
            $environments,
            $factories,
            $singleton,
            $tags
        );
    }
}
