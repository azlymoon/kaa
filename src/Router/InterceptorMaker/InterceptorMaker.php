<?php

declare(strict_types=1);

namespace Kaa\Router\InterceptorMaker;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\HttpKernel\Request;
use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\Router\Action;
use Kaa\Router\CallableAction;
use Kaa\Router\Contract\DefaultInstanceProvider;
use Kaa\Router\Exception\BadActionException;
use Kaa\Router\Exception\InterceptorException;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Router\Interceptor\AvailableVars;
use Kaa\Router\Interceptor\InterceptorGeneratorInterface;
use Kaa\Router\Interceptor\InterceptorType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use ReflectionNamedType;
use Symfony\Component\Filesystem\Filesystem;

#[PhpOnly]
class InterceptorMaker implements InterceptorMakerInterface
{
    private const CONTROLLER_RESULT_VAR_NAME = 'Router_controller_result';

    /**
     * @param Action[] $actions
     * @param mixed[] $userConfig
     * @return CallableAction[]
     * @throws InterceptorException
     * @throws BadActionException
     * @throws NoDependencyException
     */
    public function makeInterceptor(
        array $actions,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): array {
        if (empty($actions)) {
            return [];
        }

        $file = new PhpFile();
        $namespace = $file->addNamespace(rtrim($userConfig['code_gen_namespace'], '\\') . '\\Router');
        $class = $namespace->addClass('Interceptor');

        $callableActions = [];
        foreach ($actions as $action) {
            $methodName = sprintf(
                "%s_%s_%s",
                $action->reflectionClass->getShortName(),
                $action->reflectionMethod->name,
                sha1($action->reflectionClass->name . $action->reflectionMethod->name),
            );

            $method = $class->addMethod($methodName);
            $this->fillInterceptedMethod($method, $action, $userConfig, $providedDependencies);

            $varName = "Router_Interceptor_" . sha1($methodName . $namespace->getName() . '\\Interceptor');
            $newInstanceCode = sprintf('$%s = new \%s;', $varName, $namespace->getName() . '\\Interceptor');

            $callableActions[] = new CallableAction(
                $action->routes,
                $action->classRoutes,
                $methodName,
                $varName,
                $newInstanceCode
            );
        }

        $this->printFile($file, $userConfig);

        return $callableActions;
    }

    /**
     * @param mixed[] $userConfig
     * @throws InterceptorException
     * @throws BadActionException
     * @throws NoDependencyException
     */
    private function fillInterceptedMethod(
        Method $method,
        Action $action,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): void {
        $method
            ->addParameter('request')
            ->setType(Request::class);

        $method->setReturnType(ResponseInterface::class);

        $availableVars = (new AvailableVars())
            ->add(new AvailableVar('request', Request::class));

        $methodBody = [];
        foreach ($this->getBeforeGenerators($action) as $generator) {
            $methodBody[] = '// ' . $generator::class;
            $methodBody[] = $generator->generate($availableVars, $action, $userConfig, $providedDependencies);
            $methodBody[] = "\n";
        }

        $methodBody[] = '// Controller call';
        $methodBody[] = $this->generateControllerCallCode($action, $availableVars, $providedDependencies);

        $controllerReturnType = $action->reflectionMethod->getReturnType();
        if (!$controllerReturnType instanceof ReflectionNamedType || $controllerReturnType->isBuiltin()) {
            throw new BadActionException(
                sprintf(
                    'Action %s::%s must return instance of some class and not union or intersection',
                    $action->reflectionClass->name,
                    $action->reflectionMethod->name,
                )
            );
        }

        $availableVars
            ->add(
                new AvailableVar(
                    self::CONTROLLER_RESULT_VAR_NAME,
                    $controllerReturnType->getName(),
                )
            );

        foreach ($this->getAfterGenerators($action) as $generator) {
            $methodBody[] = '// ' . $generator::class;
            $methodBody[] = $generator->generate($availableVars, $action, $userConfig, $providedDependencies);
            $methodBody[] = "\n";
        }

        $responseVarName = $availableVars->getFirstByType(ResponseInterface::class)?->name
            ?? throw new InterceptorException(
                sprintf(
                    'Neither controller call nor any of the interceptors generated variable with type %s'
                    . 'to be used as a result in %s::%s',
                    ResponseInterface::class,
                    $action->reflectionClass->name,
                    $action->reflectionMethod->name,
                )
            );

        $methodBody[] = sprintf('return $%s;', $responseVarName);

        $method->addBody(implode("\n", $methodBody));
    }

    /**
     * @return InterceptorGeneratorInterface[]
     */
    private function getBeforeGenerators(Action $action): array
    {
        $generators = [];

        foreach ($action->classInterceptors as $interceptor) {
            if ($interceptor->type === InterceptorType::BEFORE) {
                $generators[] = $interceptor->generator;
            }
        }

        foreach ($action->interceptors as $interceptor) {
            if ($interceptor->type === InterceptorType::BEFORE) {
                $generators[] = $interceptor->generator;
            }
        }

        return $generators;
    }

    /**
     * @throws InterceptorException
     * @throws NoDependencyException
     */
    private function generateControllerCallCode(
        Action $action,
        AvailableVars $availableVars,
        ProvidedDependencies $providedDependencies
    ): string {
        $parameters = [];
        foreach ($action->reflectionMethod->getParameters() as $parameter) {
            $type = $parameter->getType();
            if (!$type instanceof ReflectionNamedType) {
                throw new InterceptorException(
                    sprintf(
                        'Parameter %s of %s::%s does not have type declaration or is union or intersection',
                        $parameter->name,
                        $action->reflectionClass->name,
                        $action->reflectionMethod->name,
                    )
                );
            }

            if ($availableVars->have($parameter->name, $type->getName())) {
                $parameters[] = '$' . $parameter->name;
                continue;
            }

            $availableVar = $availableVars->getFirstByType($type->getName());
            if ($availableVar !== null) {
                $parameters[] = '$' . $availableVar->name;
                continue;
            }

            throw new InterceptorException(
                sprintf(
                    'No interceptors generated variables for parameter %s of %s %s',
                    $parameter->name,
                    $action->reflectionClass->name,
                    $action->reflectionMethod->name,
                )
            );
        }

        /** @var InstanceProviderInterface $newInstanceGenerator */
        $newInstanceGenerator = $providedDependencies->get(
            InstanceProviderInterface::class,
            new DefaultInstanceProvider()
        );

        return sprintf(
            '$%s = %s->%s(%s);',
            self::CONTROLLER_RESULT_VAR_NAME,
            $newInstanceGenerator->provideInstanceCode($action->reflectionClass->name),
            $action->reflectionMethod->name,
            implode(',', $parameters),
        );
    }

    /**
     * @return InterceptorGeneratorInterface[]
     */
    private function getAfterGenerators(Action $action): array
    {
        $generators = [];

        foreach ($action->interceptors as $interceptor) {
            if ($interceptor->type === InterceptorType::AFTER) {
                $generators[] = $interceptor->generator;
            }
        }

        foreach ($action->classInterceptors as $interceptor) {
            if ($interceptor->type === InterceptorType::AFTER) {
                $generators[] = $interceptor->generator;
            }
        }

        return $generators;
    }

    /**
     * @param mixed[] $userConfig
     */
    private function printFile(PhpFile $file, array $userConfig): void
    {
        $directoryName = rtrim($userConfig['code_gen_directory'], '/') . '/Router';
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($directoryName)) {
            $fileSystem->mkdir($directoryName);
        }

        $code = (new PsrPrinter())->printFile($file);
        file_put_contents($directoryName . '/Interceptor.php', $code);
    }
}
