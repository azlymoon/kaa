<?php

declare(strict_types=1);

namespace Kaa\Router\ActionFinder;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Kaa\Router\Action;
use Kaa\Router\Attribute\Route;
use Kaa\Router\Exception\BadRouteException;
use Kaa\Router\Interceptor\EmptyInterceptor;
use Kaa\Router\Interceptor\Interceptor;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Ищет пути, определённые с помощью аттрибутов {@link Route}
 */
class AttributeActionFinder implements ActionFinderInterface
{
    public function find(array $userConfig): array
    {
        $controllerClasses = $this->findClassesInNamespaces(
            $userConfig['router']['controller_namespaces'],
            $userConfig['router']['scan_vendor'] ?? false,
        );

        return $this->buildActions($controllerClasses);
    }

    /**
     * @param string[] $namespaces
     * @return string[]
     * @throws Exception
     */
    private function findClassesInNamespaces(array $namespaces, bool $includeVendor): array
    {
        if (!$includeVendor) {
            ClassFinder::disablePSR4Vendors();
        }

        $classesInNamespaces = [];
        foreach ($namespaces as $namespace) {
            $classesInNamespaces[] = ClassFinder::getClassesInNamespace($namespace, ClassFinder::RECURSIVE_MODE);
        }

        return array_merge(...$classesInNamespaces);
    }

    /**
     * @param string[] $classNames
     * @return Action[]
     * @throws BadRouteException
     * @throws ReflectionException
     */
    private function buildActions(array $classNames): array
    {
        $actions = [];
        foreach ($classNames as $className) {
            $actions[] = $this->buildClassActions($className);
        }

        return array_merge(...$actions);
    }

    /**
     * @param string $className
     * @return Action[]
     * @throws ReflectionException
     * @throws BadRouteException
     */
    private function buildClassActions(string $className): array
    {
        $reflectionClass = new ReflectionClass($className);

        $classInterceptors = $this->getClassInterceptors($reflectionClass);
        $classRoutes = $this->getClassRoutes($reflectionClass);

        $actions = [];
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $actions[] = $this->buildMethodActions(
                $reflectionMethod,
                $reflectionClass,
                $classRoutes,
                $classInterceptors
            );
        }

        return array_filter($actions);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return Interceptor[]
     */
    private function getClassInterceptors(ReflectionClass $reflectionClass): array
    {
        $interceptorAttributes = $reflectionClass->getAttributes(
            Interceptor::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        return array_map(static fn(ReflectionAttribute $a) => $a->newInstance(), $interceptorAttributes);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return Route[]
     * @throws BadRouteException
     */
    private function getClassRoutes(ReflectionClass $reflectionClass): array
    {
        $routeAttributes = $reflectionClass->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);

        $routes = [];
        foreach ($routeAttributes as $routeAttribute) {
            $route = $routeAttribute->newInstance();

            if ($route::class !== Route::class) {
                throw new BadRouteException(
                    sprintf(
                        'Only %s attribute and NOT it`s subclasses are allowed for declaring class routing prefix. '
                        . 'But %s was specified on class %s',
                        Route::class,
                        $route::class,
                        $reflectionClass->name,
                    )
                );
            }

            if ($route->name !== null || $route->method !== null) {
                throw new BadRouteException(
                    sprintf(
                        '%s attribute on class level cannot have $name or $method set. '
                        . 'But on class %s it has',
                        Route::class,
                        $reflectionClass->name,
                    )
                );
            }

            $routes[] = $route;
        }

        return $routes;
    }

    /**
     * @param Route[] $classRoutes
     * @param Interceptor[] $classInterceptors
     */
    private function buildMethodActions(
        ReflectionMethod $reflectionMethod,
        ReflectionClass $reflectionClass,
        array $classRoutes,
        array $classInterceptors,
    ): ?Action {
        $routeAttributes = $reflectionMethod->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);

        if (empty($routeAttributes)) {
            return null;
        }

        $routes = array_map(static fn(ReflectionAttribute $a) => $a->newInstance(), $routeAttributes);

        $interceptorAttributes = $reflectionMethod->getAttributes(
            Interceptor::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        /** @var Interceptor[] $interceptors */
        $interceptors = array_map(static fn(ReflectionAttribute $a) => $a->newInstance(), $interceptorAttributes);

        if (empty($interceptors) && empty($reflectionMethod->getParameters())) {
            $interceptors[] = new EmptyInterceptor();
        }

        return new Action(
            $reflectionMethod,
            $reflectionClass,
            $routes,
            $interceptors,
            $classRoutes,
            $classInterceptors
        );
    }
}
