<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\ServiceInfo;
use Kaa\CodeGen\Contract\ServiceStorageInterface;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\HttpKernel\Event\FindActionEvent;
use Kaa\HttpKernel\EventListener\AbstractFindActionEventListener;
use Kaa\HttpKernel\HttpKernelEvents;
use Kaa\Router\CallableRoute;
use Kaa\Router\HttpRoute;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Symfony\Component\Filesystem\Filesystem;

#[PhpOnly]
class FindActionListenerGenerator implements FindActionListenerGeneratorInterface
{
    private const ROUTE_NAME_VAR = 'Router_route_name';

    /**
     * @throws NoDependencyException
     */
    public function generate(
        array $callableRoutes,
        RouteMatcherGeneratorInterface $routeMatcherGenerator,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): void {
        $phpFile = new PhpFile();
        $namespace = $phpFile->addNamespace(rtrim($userConfig['code_gen_namespace'], '\\') . '\\Router');

        $class = $namespace->addClass('Router');

        $method = $class->addMethod('__invoke');
        $method->setVisibility(ClassLike::VisibilityPublic);
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

        if ($providedDependencies->has(ServiceStorageInterface::class)) {
            $serviceInfo = new ServiceInfo(
                class: rtrim($userConfig['code_gen_namespace'], '\\') . '\\Router\\Router',
                tags: [
                    'events' => [
                        [
                            'event' => HttpKernelEvents::FIND_ACTION,
                            'method' => '__invoke',
                            'priority' => 0,
                            'dispatcher' => 'kernel.dispatcher'
                        ]
                    ]
                ]
            );

            $providedDependencies->get(ServiceStorageInterface::class)->addService($serviceInfo);
        }
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
     * @param mixed[] $userConfig
     */
    private function saveFile(PhpFile $phpFile, array $userConfig): void
    {
        $fileSystem = new Filesystem();

        if (!$fileSystem->exists(rtrim($userConfig['code_gen_directory'], '/') . '/Router')) {
            $fileSystem->mkdir(rtrim($userConfig['code_gen_directory'], '/') . '/Router');
        }

        $code = (new PsrPrinter())->printFile($phpFile);
        file_put_contents(rtrim($userConfig['code_gen_directory'], '/') . '/Router/Router.php', $code);
    }
}
