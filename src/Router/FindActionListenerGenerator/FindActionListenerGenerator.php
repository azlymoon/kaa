<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\ServiceInfo;
use Kaa\CodeGen\Contract\ServiceStorageInterface;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\HttpKernel\Event\FindActionEvent;
use Kaa\HttpKernel\HttpKernelEvents;
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

        $method = $class->addMethod('invoke');
        $method->setVisibility(ClassLike::VisibilityPublic);
        $method->addParameter('event')->setType(FindActionEvent::class);
        $method->setReturnType('void');

        $method->addBody('$route = $event->getRequest()->getRoute();');
        $method->addBody('$method = $event->getRequest()->method();');
/*
        $routes = array_map(
            static fn(CallableRoute $route) => new HttpRoute($route->path, $route->method, $route->name),
            $callableRoutes
        );
 */
        $method->addBody(
            $routeMatcherGenerator->generateMatchCode(
                self::ROUTE_NAME_VAR,
                'route',
                'method',
                $callableRoutes,
                $userConfig,
                $providedDependencies,
            )
        );

        $this->saveFile($phpFile, $userConfig);

        if ($providedDependencies->has(ServiceStorageInterface::class)) {
            $serviceInfo = new ServiceInfo(
                class: rtrim($userConfig['code_gen_namespace'], '\\') . '\\Router\\Router',
                tags: [
                    'events' => [
                        [
                            'event' => HttpKernelEvents::FIND_ACTION,
                            'method' => 'invoke',
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
     * @param PhpFile $phpFile
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
