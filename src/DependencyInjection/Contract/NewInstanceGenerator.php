<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Contract;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\NewInstanceGeneratorInterface;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Attribute\Factory;
use Kaa\DependencyInjection\Attribute\When;
use Kaa\DependencyInjection\Collection\Container;
use Kaa\DependencyInjection\Collection\ServiceDefinition;
use Kaa\DependencyInjection\Exception\BadDefinitionException;
use Kaa\DependencyInjection\ReflectionUtils;
use ReflectionClass;
use ReflectionException;

#[PhpOnly]
readonly class NewInstanceGenerator implements NewInstanceGeneratorInterface
{
    private const CREATE_SINGLETON_CODE = <<<'PHP'
    self::$%varName% = new \%className%(
        %parameters%
    );
    return self::$%varName%;
PHP;

    private const CREATE_NOT_SINGLETON_CODE = <<<'PHP'
    return new \%className%(
        %parameters%
    );
PHP;

    private const FACTORY_SINGLETON_CODE = <<<'PHP'
    self::$%varName% = %factoryCall%;
    return self::$%varName%;
PHP;

    private const FACTORY_NOT_SINGLETON_CODE = <<<'PHP'
    return %factoryCall%;
PHP;

    private const IF_ENV_CODE = <<<'PHP'
    if (self::%envMethod%()['APP_ENV'] === '%env%') {
        %code%
    }
PHP;

    private const ENVIRONMENT_CODE = <<<'PHP'
        if (self::$%methodName% !== null) {
            return self::$%methodName%;
        }

        self::$%methodName% = \Kaa\CodeGen\EnvReader::readEnv('%kernel_dir%');
        return self::$%methodName%;
PHP;

    private DiContainerGenerator $diContainerGenerator;

    /**
     * @param mixed[] $userConfig
     */
    public function __construct(
        private Container $container,
        private array $userConfig,
    ) {
        $this->diContainerGenerator = new DiContainerGenerator($this->userConfig);
    }

    /**
     * @throws CodeGenException
     * @throws ReflectionException
     */
    public function getNewInstanceCode(string $varName, string $className): string
    {
        $methodName = $this->generateService($className);
        return sprintf('\%s::%s()', $this->diContainerGenerator->getClassName(), $methodName);
    }

    /**
     * @throws ReflectionException
     * @throws CodeGenException
     */
    private function generateService(string $className): string
    {
        $services = $this->getServiceDefinitions($className);

        $methodName = 'service_' . sha1($className);
        if (!$this->diContainerGenerator->hasMethod($methodName)) {
            $this->addServiceMethod($methodName, $services, $className);
        }

        return $methodName;
    }

    /**
     * @return ServiceDefinition[]
     * @throws CodeGenException
     */
    private function getServiceDefinitions(string $className): array
    {
        return match (true) {
            $this->container->services->haveName($className)
            => $this->container->services->getByName($className),

            $this->container->services->haveAlias($className)
            => $this->container->services->getByAlias($className),

            default
            => BadDefinitionException::throw('Service %s does not exist', $className)
        };
    }

    /**
     * @param ServiceDefinition[] $services
     * @throws ReflectionException
     * @throws CodeGenException
     */
    private function addServiceMethod(string $methodName, array $services, string $serviceName): void
    {
        $commonParent = ReflectionUtils::getCommonSuperClass(
            array_map(
                static fn(ServiceDefinition $definition) => new ReflectionClass($definition->class),
                $services
            )
        );

        if ($commonParent === null) {
            BadDefinitionException::throw(
                'Services found for %s do not have common superclass',
                $serviceName
            );
        }

        $this->diContainerGenerator->addVar($commonParent, $methodName);

        $envCode = [];
        $defaultEnvCode = null;
        foreach ($services as $environment => $service) {
            if ($environment === When::DEFAULT_ENVIRONMENT) {
                $defaultEnvCode = $this->generateReturnServiceCode($methodName, $service);
            } else {
                $envCode[$environment] = $this->generateReturnServiceCode($methodName, $service);
            }
        }

        $code = $this->joinEnvironmentCode($envCode, $defaultEnvCode);

        $this->diContainerGenerator->addMethod($commonParent, $methodName, $code);
    }

    /**
     * @throws ReflectionException
     * @throws CodeGenException
     */
    private function generateReturnServiceCode(string $methodName, ServiceDefinition $service): string
    {
        if ($service->hasFactories()) {
            return $this->generateReturnServiceCodeWithFactory($methodName, $service);
        }

        return $this->generateReturnServiceCodeWithNew($methodName, $service);
    }

    /**
     * @throws CodeGenException
     * @throws ReflectionException
     */
    private function generateReturnServiceCodeWithFactory(string $methodName, ServiceDefinition $service): string
    {
        $envCode = [];
        $defaultEnvCode = null;
        foreach ($service->factories as $environment => $factory) {
            if ($environment === When::DEFAULT_ENVIRONMENT) {
                $defaultEnvCode = $this->generateFactoryCallCode($methodName, $factory, $service);
            } else {
                $envCode[$environment] = $this->generateFactoryCallCode($methodName, $factory, $service);
            }
        }

        return $this->joinEnvironmentCode($envCode, $defaultEnvCode);
    }

    /**
     * @throws ReflectionException
     * @throws CodeGenException
     */
    private function generateFactoryCallCode(string $methodName, Factory $factory, ServiceDefinition $service): string
    {
        if ($factory->isStatic) {
            $callCode = sprintf('\%s::%s()', $factory->factory, $factory->method);
        } else {
            $factoryServiceMethod = $this->generateService($factory->factory);
            $callCode = sprintf('self::%s()->%s()', $factoryServiceMethod, $factory->method);
        }

        $code = $service->isSingleton ? self::FACTORY_SINGLETON_CODE : self::FACTORY_NOT_SINGLETON_CODE;
        $replacements = [
            '%varName%' => $methodName,
            '%factoryCall%' => $callCode,
        ];

        return strtr($code, $replacements);
    }

    /**
     * @param string[] $envCode
     */
    private function joinEnvironmentCode(array $envCode, ?string $defaultCode): string
    {
        $envMethod = $this->generateEnvMethod();

        $code = [];
        foreach ($envCode as $environment => $localCode) {
            $replacements = [
                '%envMethod%' => $envMethod,
                '%env%' => $environment,
                '%code%' => $localCode
            ];

            $code[] = strtr(self::IF_ENV_CODE, $replacements);
        }

        if ($defaultCode !== null) {
            $code[] = $defaultCode;
        }

        return implode("\n\n", $code);
    }

    private function generateEnvMethod(): string
    {
        $methodName = 'environment';
        if ($this->diContainerGenerator->hasMethod($methodName)) {
            return $methodName;
        }

        $this->diContainerGenerator->addVar('mixed', $methodName);

        $replacements = [
            '%kernel_dir%' => $this->userConfig['kernel_dir'],
            '%methodName%' => $methodName,
        ];

        $this->diContainerGenerator->addMethod('mixed', $methodName, strtr(self::ENVIRONMENT_CODE, $replacements));

        return $methodName;
    }

    /**
     * @throws ReflectionException
     * @throws CodeGenException
     */
    private function generateReturnServiceCodeWithNew(string $methodName, ServiceDefinition $service): string
    {
        $parameters = [];
        foreach ($service->dependencies as $dependency) {
            if ($dependency->isService()) {
                $parameters[] = $this->generateService($dependency->injectedName ?? $dependency->type);
            } else {
                $parameters[] = $this->generateParameter(
                    $dependency->type,
                    $dependency->name,
                    $dependency->injectedName
                );
            }
        }

        $parameters = array_map(
            static fn(string $method) => sprintf('self::%s()', $method),
            $parameters
        );

        $code = $service->isSingleton ? self::CREATE_SINGLETON_CODE : self::CREATE_NOT_SINGLETON_CODE;
        $replacements = [
            '%varName%' => $methodName,
            '%className%' => $service->class,
            '%parameters%' => implode(",\n", $parameters)
        ];

        return strtr($code, $replacements);
    }

    private function generateParameter(string $type, string $name, ?string $injectedName): string
    {
        if ($injectedName !== null) {
            $methodName = $injectedName;
            $parameter = $this->container->parameters->get($injectedName);
        } else {
            $methodName = $type . $name;
            $parameter = $this->container->parameters->getByBinding($type, $name);
        }

        $methodName = 'parameter_' . sha1($methodName);
        if ($this->diContainerGenerator->hasMethod($methodName)) {
            return $methodName;
        }

        if ($parameter->isEnvVar) {
            $code = sprintf('return self::%s()[%s];', $this->generateEnvMethod(), $parameter->envVarName);
        } else {
            $code = sprintf('return (%s) %s;', $type, $parameter->value);
        }

        $this->diContainerGenerator->addMethod($type, $methodName, $code);

        return $methodName;
    }

    public function dump(): void
    {
        $this->diContainerGenerator->dump();
    }
}
