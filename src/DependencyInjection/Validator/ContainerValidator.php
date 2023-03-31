<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Validator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\CodeGen\Exception\InvalidDependencyException;
use Kaa\DependencyInjection\Attribute\Factory;
use Kaa\DependencyInjection\Attribute\When;
use Kaa\DependencyInjection\Collection\Container;
use Kaa\DependencyInjection\Collection\Dependency\Dependency;
use Kaa\DependencyInjection\Collection\Service\EnvAwareServiceCollection;
use Kaa\DependencyInjection\Collection\Service\ServiceDefinition;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

#[PhpOnly]
class ContainerValidator implements ContainerValidatorInterface
{
    /**
     * @throws CodeGenException
     * @throws ReflectionException
     */
    public function validate(Container $container): void
    {
        $errors = [];
        foreach ($container->services as $service) {
            if ($service->hasFactories()) {
                $errors[] = $this->validateFactories($service, $container);
            } else {
                $errors[] = $this->validateDependencies($service, $container);
            }
        }

        $errors = array_merge(...$errors);
        if (!empty($errors)) {
            InvalidDependencyException::throw(implode("\n\n", $errors));
        }
    }

    /**
     * @return string[] Сообщения об ошибках
     * @throws ReflectionException
     * @throws CodeGenException
     */
    private function validateFactories(ServiceDefinition $service, Container $container): array
    {
        $errors = [];

        if ($service->factories->hasEnvironmentFactories() && $service->environments !== [When::DEFAULT_ENVIRONMENT]) {
            $errors[] = sprintf(
                'Service %s must not define both environments ("when") and environment dependent factories',
                $service->name
            );
        }

        foreach ($service->factories as $factory) {
            if (!class_exists($factory->factory) && $factory->isStatic) {
                $errors[] = sprintf(
                    'Service %s defines static factory %s, but factory class does not exist '
                    . '(Or you define static factory by service name)',
                    $service->name,
                    $factory->factory
                );

                continue;
            }

            if ($factory->isStatic) {
                $factoryClass = $factory->factory;
            } else {
                [$factoryClass, $factoryErrors] = $this->getFactoryClass($factory, $service, $container);

                if (!empty($factoryErrors)) {
                    $errors = [...$errors, ...$factoryErrors];
                    continue;
                }
            }

            $reflectionClass = new ReflectionClass($factoryClass);
            if (!$reflectionClass->hasMethod($factory->method)) {
                $errors[] = sprintf(
                    'Service %s requires factory %s with method %s, but the method does not exist',
                    $service->name,
                    $factory->factory,
                    $factory->method
                );
                continue;
            }

            $reflectionMethod = $reflectionClass->getMethod($factory->method);
            if ($factory->isStatic !== $reflectionMethod->isStatic()) {
                $errors[] = sprintf(
                    'Factory %s of service %s %s static, but it`s method %s %s',
                    $factory->factory,
                    $service->name,
                    $factory->isStatic ? 'is' : 'is not',
                    $factory->method,
                    $reflectionMethod->isStatic() ? 'is' : 'is not',
                );
                continue;
            }

            $returnType = $reflectionMethod->getReturnType();
            if (!$returnType instanceof ReflectionNamedType) {
                $errors[] = sprintf(
                    'Service %s requires factory %s with method %s, '
                    . 'but the method does not declare return type or declares it as a union or an intersection',
                    $service->name,
                    $factory->factory,
                    $factory->method,
                );
                continue;
            }

            if (!is_a($returnType->getName(), $service->class, true)) {
                $errors[] = sprintf(
                    'Service %s requires factory %s with method %s, but the method returns %s, '
                    . 'which is not a subclass of service`s class %s',
                    $service->name,
                    $factory->factory,
                    $factory->method,
                    $returnType->getName(),
                    $service->class,
                );
            }
        }

        return $errors;
    }

    /**
     * @return string[] Сообщения об ошибках
     * @throws CodeGenException
     */
    private function validateDependencies(ServiceDefinition $service, Container $container): array
    {
        $errors = [];
        foreach ($service->dependencies as $dependency) {
            // Зависимость является параметром
            if ($dependency->isParameter()) {
                if ($dependency->isInjected()) {
                    if (!$container->parameters->have($dependency->injectedName)) {
                        $errors[] = sprintf(
                            'Service %s depends on parameter injected as %s, '
                            . 'but parameter with such name is not defined',
                            $service->name,
                            $dependency->injectedName,
                        );
                    }

                    continue;
                }

                if (!$container->parameters->haveBinding($dependency->type, $dependency->name)) {
                    $errors[] = sprintf(
                        'Service %s depends on parameter binded as %s $%s, '
                        . 'but parameter with such binding is not defined',
                        $service->name,
                        $dependency->type,
                        $dependency->name
                    );
                }

                continue;
            }

            if (!$dependency->isService()) {
                $errors[] = sprintf(
                    'Service %s defines dependency %s $%s%s, which is neither a service nor a parameter '
                    . '(probably wrong type like "resource" etc)',
                    $service->name,
                    $dependency->type,
                    $dependency->injectedName !== '' ? " \"$dependency->injectedName\" " : '',
                    $dependency->name,
                );
            }

            $name = $dependency->isInjected() ? $dependency->injectedName : $dependency->type;

            if (!$container->services->have($name)) {
                $errors[] = sprintf(
                    'Service %s depends on service %s, but such service does not exist',
                    $service->name,
                    $dependency->type
                );
            }

            $implementations = $container->services->get($name);
            if ($implementations->areDubious()) {
                $possibleImplementationsString = $this->buildPossibleImplementationsString(
                    $implementations
                );

                $errors[] = sprintf(
                    'Service %s depends on service %s for which exists multiple implementations: %s',
                    $service->name,
                    $dependency->type,
                    $possibleImplementationsString
                );

                continue;
            }

            $errors = [...$errors, ...$this->validateImplementations($service, $dependency, $implementations)];
        }

        return $errors;
    }

    /**
     * @return string[]
     */
    private function validateImplementations(
        ServiceDefinition $service,
        Dependency $dependency,
        EnvAwareServiceCollection $implementations
    ): array {
        $errors = [];

        foreach ($implementations as $environment => $implementation) {
            if (!is_a($implementation->class, $dependency->type, true)) {
                $errors[] = sprintf(
                    'Service %s depends on service "%s", but it`s implementation %s for environment "%s" '
                    . 'is not a subclass of service`s dependency class %s',
                    $service->name,
                    $dependency->injectedName,
                    $implementation->class,
                    $environment,
                    $dependency->type,
                );
            }
        }

        return $errors;
    }

    private function buildPossibleImplementationsString(EnvAwareServiceCollection $possibleImplementations): string
    {
        $strings = [''];

        foreach ($possibleImplementations->getAll() as $environment => $implementations) {
            foreach ($implementations as $implementation) {
                $strings[] = sprintf('Environment: "%s" => Service: %s', $environment, $implementation->name);
            }
        }

        return implode("\n", $strings);
    }

    /**
     * @return array{0: string|null, 1: string[]}
     * @throws CodeGenException
     */
    private function getFactoryClass(Factory $factory, ServiceDefinition $service, Container $container): array
    {
        if (!$container->services->have($factory->factory)) {
            $error = sprintf(
                'Service "%s" requires factory service "%s", but it does not exist',
                $service->name,
                $factory->factory
            );

            return [null, [$error]];
        }

        $factories = $container->services->get($factory->factory);
        if (!$factories->haveOnlyDefaultEnvironment() || $factories->areDubious()) {
            $error = sprintf(
                'Service "%s" requires factory service "%s", but it is environment dependent or dubious. '
                . 'If you need different factories for different environments, use "when" is #[Factory]',
                $service->name,
                $factory->factory
            );

            return [null, [$error]];
        }

        return [$factories->getOnlyByEnvironment(When::DEFAULT_ENVIRONMENT)->class, []];
    }
}
