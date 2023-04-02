<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\EventDispatcherLinker;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\BootstrapProviderInterface;
use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\GeneratorInterface;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\DependencyInjection\Attribute\When;
use Kaa\DependencyInjection\Collection\Service\ServiceDefinition;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\DependencyInjection\DependencyInjectionGenerator;
use Kaa\DependencyInjection\Exception\EventDispatcherLinkerException;
use Kaa\EventDispatcher\EventInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

#[PhpOnly]
class EventDispatcherLinkerGenerator implements GeneratorInterface
{
    private const ADD_LISTENER_CODE = <<<'PHP'
%dispatcher%->addListener('%eventName%', %callable%, %priority%);
PHP;

    private const IF_ENV_CODE = <<<'PHP'
if (in_array(%getEnv%, [%environments%], true)) {
%code%
}
PHP;

    private InstanceProvider $instanceProvider;

    private BootstrapProviderInterface $bootstrapProvider;

    private EventListenerProxyGenerator $eventListenerProxyGenerator;

    /**
     * @throws CodeGenException
     * @throws ReflectionException
     */
    public function generate(array $userConfig, ProvidedDependencies $providedDependencies): void
    {
        $this->eventListenerProxyGenerator = new EventListenerProxyGenerator($userConfig);
        $this->instanceProvider = $this->getInstanceProvider($providedDependencies);
        $this->bootstrapProvider = $this->getBootstrapProvider($providedDependencies);

        $services = $this->instanceProvider->container->services->getServicesByTagName('events');
        foreach ($services as $service) {
            $this->linkListener($service);
        }

        $this->eventListenerProxyGenerator->dump();
    }

    /**
     * @throws NoDependencyException
     * @throws CodeGenException
     */
    private function getInstanceProvider(ProvidedDependencies $providedDependencies): InstanceProvider
    {
        if (!$providedDependencies->has(InstanceProviderInterface::class)) {
            EventDispatcherLinkerException::throw(
                'You must execute %s before executing %s',
                DependencyInjectionGenerator::class,
                self::class
            );
        }

        $instanceProvider = $providedDependencies->get(InstanceProviderInterface::class);
        if (!$instanceProvider instanceof InstanceProvider) {
            EventDispatcherLinkerException::throw(
                'There is implementation %s of %s in provided dependencies, '
                . 'but %s is compatible only with %s provided by %s',
                $instanceProvider::class,
                InstanceProviderInterface::class,
                self::class,
                InstanceProvider::class,
                DependencyInjectionGenerator::class
            );
        }

        return $instanceProvider;
    }

    /**
     * @throws NoDependencyException
     * @throws CodeGenException
     */
    private function getBootstrapProvider(ProvidedDependencies $providedDependencies): BootstrapProviderInterface
    {
        if (!$providedDependencies->has(BootstrapProviderInterface::class)) {
            EventDispatcherLinkerException::throw(
                '%s requires %s to be provided',
                self::class,
                BootstrapProviderInterface::class,
            );
        }

        return $providedDependencies->get(BootstrapProviderInterface::class);
    }

    /**
     * @throws CodeGenException
     * @throws ReflectionException
     */
    private function linkListener(ServiceDefinition $service): void
    {
        $reflectionClass = new ReflectionClass($service->class);
        if (!$reflectionClass->isInstantiable()) {
            EventDispatcherLinkerException::throw(
                'Service "%s" defines tag "events" but is not instantiatable',
                $service->name
            );
        }

        foreach ($service->tags['events'] ?? [] as $event) {
            $this->addListener(
                $event['dispatcher'],
                $event['event'],
                $event['method'],
                $event['priority'],
                $reflectionClass,
                $service
            );
        }
    }

    /**
     * @throws ReflectionException
     * @throws CodeGenException
     */
    private function addListener(
        string $dispatcherName,
        string $eventName,
        string $methodName,
        int $priority,
        ReflectionClass $reflectionClass,
        ServiceDefinition $service
    ): void {
        if (!$reflectionClass->hasMethod($methodName)) {
            EventDispatcherLinkerException::throw(
                'Service "%s" is event listener, but does not define method "%s" to handle the event',
                $service->name,
                $methodName
            );
        }

        $reflectionMethod = $reflectionClass->getMethod($methodName);
        if (!$reflectionMethod->isPublic()) {
            EventDispatcherLinkerException::throw(
                'Method "%s" of service "%s" must be public to be able to handle events',
                $methodName,
                $service->name
            );
        }

        $parameterType = $this->getParameterType($reflectionMethod, $service);

        $callableCode = $this->eventListenerProxyGenerator->generateProxy(
            $parameterType,
            $service->name,
            $reflectionMethod->name,
            $this->instanceProvider,
        );

        $replacements = [
            '%dispatcher%' => $this->instanceProvider->provideInstanceCode($dispatcherName),
            '%eventName%' => $eventName,
            '%callable%' => $callableCode,
            '%priority%' => $priority
        ];

        $addListenerCode = strtr(
            self::ADD_LISTENER_CODE,
            $replacements
        );

        if ($service->environments !== [When::DEFAULT_ENVIRONMENT]) {
            $environments = array_map(
                static fn(string $env) => sprintf("'%s'", $env),
                $service->environments
            );

            $environmentsString = implode(', ', $environments);
            $replacements = [
                '%getEnv%' => $this->instanceProvider->provideGetEnvCode(),
                '%environments%' => $environmentsString,
                '%code%' => $addListenerCode
            ];

            $addListenerCode = strtr(self::IF_ENV_CODE, $replacements);
        }

        $this->bootstrapProvider->addCode($addListenerCode);
    }

    /**
     * @throws CodeGenException
     */
    private function getParameterType(ReflectionMethod $reflectionMethod, ServiceDefinition $service): string
    {
        $parameters = $reflectionMethod->getParameters();
        if (count($parameters) !== 1) {
            EventDispatcherLinkerException::throw(
                'Method "%s" of service "%s" must have single parameter - the event to be handled',
                $reflectionMethod->name,
                $service->name
            );
        }

        $parameter = reset($parameters);
        $parameterType = $parameter->getType();
        if (!$parameterType instanceof ReflectionNamedType) {
            EventDispatcherLinkerException::throw(
                'Parameter of method "%s" of service "%s" must have type and not be a union or intersection',
                $reflectionMethod->name,
                $service->name
            );
        }

        $parameterTypeName = $parameterType->getName();
        if (!is_a($parameterTypeName, EventInterface::class, true)) {
            EventDispatcherLinkerException::throw(
                'Parameter of method "%s" of service "%s" must be a subclass of %s',
                $reflectionMethod->name,
                $service->name,
                EventInterface::class
            );
        }

        return $parameterTypeName;
    }
}
