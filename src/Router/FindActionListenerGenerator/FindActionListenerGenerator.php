<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\HttpKernel\Event\FindActionEvent;
use Kaa\HttpKernel\EventListener\AbstractFindActionEventListener;
use Kaa\Router\CallableRoute;
use Kaa\Router\HttpRoute;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

#[PhpOnly]
class FindActionListenerGenerator implements FindActionListenerGeneratorInterface
{
    private const ROUTE_NAME_VAR = 'Router_route_name';

    public function generate(
        array $callableRoutes,
        RouteMatcherGeneratorInterface $routeMatcherGenerator,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): void {
        $phpFile = new PhpFile();
        $namespace = $phpFile->addNamespace($userConfig['router']['find_action_listener_namespace']);

        $class = $namespace->addClass($userConfig['router']['find_action_listener_class_name']);
        $class->setExtends(AbstractFindActionEventListener::class);

        $method = $class->addMethod('handleFindAction');
        $method->setVisibility(ClassLike::VisibilityProtected);
        $method->addParameter('event')->setType(FindActionEvent::class);
        $method->setReturnType('void');

        $method->addBody('$route = $event->getRequest()->getRoute();');
        $method->addBody('$method = $event->getRequest()->method();');

        $routes = array_map(
            static fn(CallableRoute $route) => new HttpRoute($route->path, $route->method, $route->name),
            $callableRoutes
        );
        $method->addBody(
            $routeMatcherGenerator->generateMatchCode(
                self::ROUTE_NAME_VAR,
                'route',
                'method',
                $routes,
                $userConfig,
                $providedDependencies,
            )
        );

        $method->addBody($this->generateSetCode($callableRoutes));

        $this->saveFile($phpFile, $userConfig);
    }

    /**
     * @param CallableRoute[] $callableRoutes
     */
    private function generateSetCode(array $callableRoutes): string
    {
        $code = [];

        foreach ($callableRoutes as $callableRoute) {
            $routeCode = <<<'PHP'
if ($%s === '%s') {
    %s
    $event->setAction([$%s, '%s']);
    $event->stopPropagation();
    return;
}
PHP;

            $routeCode = sprintf(
                $routeCode,
                self::ROUTE_NAME_VAR,
                $callableRoute->name,
                $callableRoute->newInstanceCode,
                $callableRoute->varName,
                $callableRoute->methodName,
            );

            $code[] = $routeCode;
        }

        return implode("\n\n", $code);
    }

    /**
     * @param PhpFile $phpFile
     * @param mixed[] $userConfig
     */
    private function saveFile(PhpFile $phpFile, array $userConfig): void
    {
        $fileSystem = new Filesystem();

        if (!$fileSystem->exists(Path::getDirectory($userConfig['router']['find_action_listener_file']))) {
            $fileSystem->mkdir(Path::getDirectory($userConfig['router']['find_action_listener_file']));
        }

        $code = (new PsrPrinter())->printFile($phpFile);
        file_put_contents($userConfig['router']['find_action_listener_file'], $code);
    }
}
