<?php

declare(strict_types=1);

namespace Kaa\Router;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\GeneratorInterface;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\Router\ActionFinder\ActionFinderInterface;
use Kaa\Router\ActionFinder\AttributeActionFinder;
use Kaa\Router\Attribute\Route;
use Kaa\Router\FindActionListenerGenerator\FindActionListenerGenerator;
use Kaa\Router\FindActionListenerGenerator\FindActionListenerGeneratorInterface;
use Kaa\Router\FindActionListenerGenerator\RouteMatcherGenerator;
use Kaa\Router\FindActionListenerGenerator\RouteMatcherGeneratorInterface;
use Kaa\Router\InterceptorMaker\InterceptorMaker;
use Kaa\Router\InterceptorMaker\InterceptorMakerInterface;

#[PhpOnly]
final readonly class RouterGenerator implements GeneratorInterface
{
    /**
     * @param ActionFinderInterface[] $actionFinders
     */
    public function __construct(
        private array $actionFinders = [new AttributeActionFinder()],
        private InterceptorMakerInterface $interceptorMaker = new InterceptorMaker(),
        private FindActionListenerGeneratorInterface $findActionListenerGenerator = new FindActionListenerGenerator(),
        private RouteMatcherGeneratorInterface $routeMatcherGenerator = new RouteMatcherGenerator(),
    ) {
    }

    /**
     * @throws Exception
     */
    public function generate(array $userConfig, ProvidedDependencies $providedDependencies): void
    {
        $actions = $this->findActions($userConfig);

        $callableActions = $this->interceptorMaker->makeInterceptor(
            $actions,
            $userConfig,
            $providedDependencies,
        );

        $callableRoutes = $this->generateCallableRoutes($callableActions);

        $this->findActionListenerGenerator->generate(
            $callableRoutes,
            $this->routeMatcherGenerator,
            $userConfig,
            $providedDependencies,
        );
    }

    /**
     * @param mixed[] $userConfig
     * @return Action[]
     * @throws Exception
     */
    private function findActions(array $userConfig): array
    {
        $actions = [];

        foreach ($this->actionFinders as $actionFinder) {
            $actions[] = $actionFinder->find($userConfig);
        }

        return array_merge(...$actions);
    }

    /**
     * @param CallableAction[] $callableActions
     * @return CallableRoute[]
     */
    private function generateCallableRoutes(array $callableActions): array
    {
        $callableRoutes = [];

        foreach ($callableActions as $callableAction) {
            $callableRoutes[] = $this->generateCallableRoutesForAction($callableAction);
        }

        return array_merge(...$callableRoutes);
    }

    /**
     * @return CallableRoute[]
     */
    private function generateCallableRoutesForAction(CallableAction $callableAction): array
    {
        $classRoutes = array_map(
            fn(Route $route) => $this->normalizeRoute($route->route),
            $callableAction->classRoutes,
        );

        if (empty($classRoutes)) {
            $classRoutes[] = '';
        }

        $callableRoutes = [];
        foreach ($classRoutes as $classRoute) {
            foreach ($callableAction->routes as $route) {
                $path = $classRoute . $this->normalizeRoute($route->route);
                $name = $route->name ?? sha1($route->method . $path);

                $callableRoutes[] = new CallableRoute(
                    $path,
                    $route->method,
                    $name,
                    $callableAction->methodName,
                    $callableAction->varName,
                    $callableAction->newInstanceCode,
                );
            }
        }

        return $callableRoutes;
    }

    private function normalizeRoute(string $route): string
    {
        return '/' . trim($route, '/');
    }
}
