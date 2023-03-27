<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Validator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\CodeGen\Exception\InvalidDependencyException;
use Kaa\DependencyInjection\Collection\Container;
use Kaa\DependencyInjection\Collection\Dependency;
use Kaa\DependencyInjection\Collection\ServiceDefinition;
use ReflectionClass;
use ReflectionNamedType;

#[PhpOnly]
class ContainerValidator implements ContainerValidatorInterface
{
    /**
     * @throws CodeGenException
     */
    public function validate(Container $container): void
    {
        $errors = [];
        foreach ($container->services as $service) {
            if ($service->hasFactories()) {
                $errors[] = $this->validateFactories($service);
            } else {
                $errors[] = $this->validateDependencies($service, $container);
            }
        }

        $errors = array_merge(...$errors);
        if (!empty($errors)) {
            InvalidDependencyException::throw(implode("\n", $errors));
        }
    }

    /**
     * @return string[] Сообщения об ошибках
     */
    private function validateFactories(ServiceDefinition $service): array
    {
        $errors = [];

        foreach ($service->factories as $factory) {
            if (!class_exists($factory->factory)) {
                $errors[] = sprintf(
                    'Service %s requires factory %s but factory class does not exist',
                    $service->name,
                    $factory->factory
                );
                continue;
            }

            $reflectionClass = new ReflectionClass($factory->factory);
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
     */
    private function validateDependencies(ServiceDefinition $service, Container $container): array
    {
        $errors = [];
        foreach ($service->dependencies as $dependency) {
            // Зависимость является параметром
            if ($dependency->isParameter()) {
                if ($dependency->isInjected() && !$container->parameters->have($dependency->injectedName)) {
                    $errors[] = sprintf(
                        'Service %s depends on parameter injected as %s but parameter with such name is not defined',
                        $service->name,
                        $dependency->injectedName,
                    );
                } elseif (!$container->parameters->haveBinding($dependency->type, $dependency->name)) {
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
                    'Service %s defines dependency %s, which is neither a service nor a parameter '
                    . '(probably wrong type like "resource" etc)',
                    $service->name,
                    $dependency->name,
                );
            }

            // Зависимость является сервисом и указан аттрибут #[Inject]
            if ($dependency->isInjected()) {
                if (!$container->services->haveName($dependency->injectedName)) {
                    $errors[] = sprintf(
                        'Service %s depends on service %s, but service with such name is not defined',
                        $service->name,
                        $dependency->injectedName
                    );
                    continue;
                }

                $implementations = $container->services->getByName($dependency->injectedName);
                $errors = [...$errors, ...$this->validateImplementations($service, $dependency, $implementations)];

                continue;
            }

            // Зависимость является сервисом и НЕ указан аттрибут #[Inject]
            if ($container->services->haveName($dependency->type)) {
                $implementations = $container->services->getByName($dependency->type);
                $errors = [...$errors, ...$this->validateImplementations($service, $dependency, $implementations)];

                continue;
            }

            if ($container->services->haveAlias($dependency->type)) {
                if ($container->services->isDubiousAlias($dependency->type)) {
                    $possibleImplementations = $container->services->getAllByAlias($dependency->type);
                    $possibleImplementationsString = $this->buildPossibleImplementationsString(
                        $possibleImplementations
                    );

                    $errors[] = sprintf(
                        'Service %s depends on service %s for which exists multiple implementations: %s',
                        $service->name,
                        $dependency->type,
                        $possibleImplementationsString
                    );

                    continue;
                }

                $implementations = $container->services->getByAlias($dependency->type);
                $errors = [...$errors, ...$this->validateImplementations($service, $dependency, $implementations)];

                continue;
            }

            $errors[] = sprintf(
                'Service %s depends on service %s, but such service does not exist',
                $service->name,
                $dependency->type
            );
        }

        return $errors;
    }

    /**
     * @param ServiceDefinition[] $implementations
     * @return string[] Сообщения об ошибках
     */
    private function validateImplementations(
        ServiceDefinition $service,
        Dependency $dependency,
        array $implementations
    ): array {
        $errors = [];

        foreach ($implementations as $environment => $implementation) {
            if (!is_a($implementation->class, $dependency->type, true)) {
                $errors[] = sprintf(
                    'Service %s depends on service %s, but it`s implementation %s for environment "%s" '
                    . 'is not a subclass of service`s class %s',
                    $service->name,
                    $dependency->injectedName,
                    $implementation->class,
                    $environment,
                    $service->class,
                );
            }
        }

        return $errors;
    }

    /**
     * @param ServiceDefinition[][] $possibleImplementations
     * @return string
     */
    private function buildPossibleImplementationsString(array $possibleImplementations): string
    {
        $strings = [''];

        foreach ($possibleImplementations as $environment => $implementations) {
            foreach ($implementations as $implementation) {
                $strings[] = sprintf('Environment: "%s" => Service: %s', $environment, $implementation->name);
            }
        }

        return implode("\n", $strings);
    }
}
