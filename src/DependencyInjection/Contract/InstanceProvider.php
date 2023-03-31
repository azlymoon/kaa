<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Contract;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Attribute\Factory;
use Kaa\DependencyInjection\Attribute\When;
use Kaa\DependencyInjection\Collection\Container;
use Kaa\DependencyInjection\Collection\Service\EnvAwareServiceCollection;
use Kaa\DependencyInjection\Collection\Service\ServiceDefinition;
use Kaa\DependencyInjection\Exception\BadDefinitionException;
use Kaa\DependencyInjection\Exception\DependencyNotFoundException;
use ReflectionException;

#[PhpOnly]
readonly class InstanceProvider implements InstanceProviderInterface
{
    private const CREATE_SINGLETON_CODE = <<<'PHP'
if (self::$%varName% !== null) {
    return self::$%varName%;
}

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
if (self::$%varName% !== null) {
    return self::$%varName%;
}

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
if (self::$%varName% !== null) {
    return self::$%varName%;
}

self::$%varName% = \Kaa\CodeGen\EnvReader::readEnv('%kernelDir%');
return self::$%varName%;
PHP;

    private const THROW_ENV_EXCEPTION_CODE = <<<'PHP'
throw new \Kaa\DependencyInjection\Exception\EnvImplementationNotFoundException(
    'Implementation of service "%serviceName%" for environment "' . self::%envMethod%()['APP_ENV'] . '" not found'
);
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
    public function provideInstanceCode(string $className): string
    {
        $methodName = $this->generateService($className);
        return sprintf('\%s::%s()', $this->diContainerGenerator->getFqnClassName(), $methodName);
    }

    /**
     * @throws ReflectionException
     * @throws CodeGenException
     */
    private function generateService(string $classOrName): string
    {
        if (!$this->container->services->have($classOrName)) {
            DependencyNotFoundException::throw('Service "%s" does not exist', $classOrName);
        }

        $services = $this->container->services->get($classOrName);
        $commonParent = $services->getCommonParent();

        if ($commonParent === null) {
            BadDefinitionException::throw(
                'Services found for "%s" do not have common superclass',
                $classOrName
            );
        }

        if (class_exists($classOrName) || interface_exists($classOrName)) {
            $methodName = 'service_by_type_' . sha1($commonParent);
        } else {
            $methodName = 'service_by_name_' . sha1($classOrName);
        }

        if (!$this->diContainerGenerator->hasMethod($methodName)) {
            $this->addServiceMethod($methodName, $services, $classOrName, $commonParent);
        }

        return $methodName;
    }

    /**
     * @throws ReflectionException
     * @throws CodeGenException
     */
    private function addServiceMethod(
        string $methodName,
        EnvAwareServiceCollection $services,
        string $serviceName,
        string $commonParent
    ): void {
        $this->diContainerGenerator->addVar($commonParent, $methodName);

        $envCode = [];
        $defaultEnvCode = null;
        foreach ($services as $environment => $service) {
            if ($environment === When::DEFAULT_ENVIRONMENT) {
                $defaultEnvCode = $this->generateReturnServiceCode($methodName, $service, $serviceName);
            } else {
                $envCode[$environment] = $this->generateReturnServiceCode($methodName, $service, $serviceName);
            }
        }

        $code = $this->joinEnvironmentCode($envCode, $defaultEnvCode, $serviceName);
        $comment = sprintf("// Service %s\n", $serviceName);

        $this->diContainerGenerator->addMethod($commonParent, $methodName, $comment . $code);
    }

    /**
     * @throws ReflectionException
     * @throws CodeGenException
     */
    private function generateReturnServiceCode(
        string $methodName,
        ServiceDefinition $service,
        string $serviceName
    ): string {
        if ($service->hasFactories()) {
            return $this->generateReturnServiceCodeWithFactory($methodName, $service, $serviceName);
        }

        return $this->generateReturnServiceCodeWithNew($methodName, $service);
    }

    /**
     * @throws CodeGenException
     * @throws ReflectionException
     */
    private function generateReturnServiceCodeWithFactory(
        string $methodName,
        ServiceDefinition $service,
        string $serviceName
    ): string {
        $envCode = [];
        $defaultEnvCode = null;
        foreach ($service->factories as $environment => $factory) {
            if ($environment === When::DEFAULT_ENVIRONMENT) {
                $defaultEnvCode = $this->generateFactoryCallCode($methodName, $factory, $service);
            } else {
                $envCode[$environment] = $this->generateFactoryCallCode($methodName, $factory, $service);
            }
        }

        return $this->joinEnvironmentCode($envCode, $defaultEnvCode, $serviceName);
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
    private function joinEnvironmentCode(array $envCode, ?string $defaultCode, string $serviceName): string
    {
        $code = [];
        $throwEnvCode = '';

        if (!empty($envCode)) {
            $envMethod = $this->generateEnvMethod();
            $replacements = [
                '%envMethod%' => $envMethod,
                '%serviceName%' => $serviceName,
            ];
            $throwEnvCode = strtr(self::THROW_ENV_EXCEPTION_CODE, $replacements);

            foreach ($envCode as $environment => $localCode) {
                $replacements = [
                    '%envMethod%' => $envMethod,
                    '%env%' => $environment,
                    '%code%' => $localCode
                ];

                $code[] = strtr(self::IF_ENV_CODE, $replacements);
            }
        }

        if ($defaultCode !== null) {
            $code[] = $defaultCode;
        } else {
            $code[] = $throwEnvCode;
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
            '%kernelDir%' => $this->userConfig['kernel_dir'],
            '%varName%' => $methodName,
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
                $name = !empty($dependency->injectedName) ? $dependency->injectedName : $dependency->type;
                $parameters[] = $this->generateService($name);
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
            '%parameters%' => implode(",\n\t", $parameters)
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

        $comment = sprintf("// Parameter %s $%s %s\n", $type, $name, $injectedName);
        $this->diContainerGenerator->addMethod($type, $methodName, $comment . $code);

        return $methodName;
    }

    public function dump(): void
    {
        $this->diContainerGenerator->dump();
    }
}
