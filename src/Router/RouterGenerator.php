<?php

declare(strict_types=1);

namespace Kaa\Router;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\NewInstanceGeneratorInterface;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\GeneratorInterface;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\Router\ActionFinder\ActionFinderInterface;
use Kaa\Router\ActionFinder\AttributeActionFinder;
use Kaa\Router\Attribute\Route;
use Kaa\Router\Contract\DefaultNewInstanceGenerator;
use Kaa\Router\Exception\BadActionException;
use Kaa\Router\Exception\InterceptorException;
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
        $callableActions = $this->generateCallableActions($actions, $userConfig, $providedDependencies);
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
     * @param Action[] $actions
     * @param mixed[] $userConfig
     * @return CallableAction[]
     * @throws InterceptorException
     * @throws NoDependencyException
     * @throws BadActionException
     */
    private function generateCallableActions(
        array $actions,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): array {
        $plainActions = [];
        $interceptedCallableActions = [];
        foreach ($actions as $action) {
            if ($action->hasInterceptors()) {
                $interceptedCallableActions[] = $action;
            } else {
                $plainActions[] = $action;
            }
        }

        $interceptedCallableActions = $this->interceptorMaker->makeInterceptors(
            $interceptedCallableActions,
            $userConfig,
            $providedDependencies,
        );

        $plainCallableActions = $this->generatePlainCallableActions($plainActions, $providedDependencies);


        return array_merge($interceptedCallableActions, $plainCallableActions);
    }

    /**
     * @param Action[] $actions
     * @return CallableAction[]
     * @throws NoDependencyException
     */
    private function generatePlainCallableActions(array $actions, ProvidedDependencies $providedDependencies): array
    {
        $newInstanceGenerator = $providedDependencies->get(
            NewInstanceGeneratorInterface::class,
            new DefaultNewInstanceGenerator()
        );

        $generateVarName = static fn(Action $action) => "Router_Controller_" . sha1(
            $action->reflectionMethod->name . $action->reflectionClass->name
        );

        return array_map(
            static fn(Action $action) => new CallableAction(
                $action->routes,
                $action->classRoutes,
                $action->reflectionMethod->name,
                $generateVarName($action),
                $newInstanceGenerator->getNewInstanceCode($generateVarName($action), $action->reflectionClass->name)
            ),
            $actions,
        );
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
