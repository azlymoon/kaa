<?php

namespace Kaa\CorsBundle;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\BootstrapProviderInterface;
use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\GeneratorInterface;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\CorsBundle\Providers\CorsProvider;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\DependencyInjection\DependencyInjectionGenerator;
use Kaa\DependencyInjection\Exception\EventDispatcherLinkerException;
use PhpParser\Node\Expr\Array_;
use ReflectionException;

#[PhpOnly]
class CorsGenerator implements GeneratorInterface
{
    private const ADD_LISTENER_CODE = <<<'PHP'
%dispatcher%->addListener('%eventName%', %callable%, %priority%);
PHP;

    private InstanceProvider $instanceProvider;

    private BootstrapProviderInterface $bootstrapProvider;

    private CorsProvider $corsProvider;

    public function __construct()
    {
    }

    /**
     * @throws NoDependencyException
     * @throws CodeGenException
     * @throws ReflectionException
     */
    public function generate(array $userConfig, ProvidedDependencies $providedDependencies): void
    {
        $this->bootstrapProvider = $this->getBootstrapProvider($providedDependencies);
        $this->instanceProvider = $this->getInstanceProvider($providedDependencies);
        $this->corsProvider = new CorsProvider($userConfig);
        $replacements = [
            '%dispatcher%' => $this->instanceProvider->provideInstanceCode('kernel.dispatcher'),
            '%eventName%' => 'http.kernel.find.action',
            '%callable%' => "[ \Kaa\CorsBundle\PvpenderCorsBundle::class, 'checkOptions']",
            '%priority%' => 0
        ];
        $this->bootstrapProvider->addCode(strtr(self::ADD_LISTENER_CODE, $replacements));
        $replacements = [
            '%dispatcher%' => $this->instanceProvider->provideInstanceCode('kernel.dispatcher'),
            '%eventName%' => 'http.kernel.response',
            '%callable%' => "[ \KaaGenerated\Cors\EventListenerCors::class, 'addHeaders']",
            '%priority%' => 0
        ];
        $this->bootstrapProvider->addCode(strtr(self::ADD_LISTENER_CODE, $replacements));
        $this->generateConfigCode($userConfig);
        $this->corsProvider->dump();
    }

    /**
     * @throws NoDependencyException
     * @throws CodeGenException
     */
    private function getBootstrapProvider(ProvidedDependencies $providedDependencies): BootstrapProviderInterface
    {
        if (!$providedDependencies->has(BootstrapProviderInterface::class)) {
            EventDispatcherLinkerException::throw(
                '%s requires %s to be provided',
                self::class,
                BootstrapProviderInterface::class,
            );
        }

        return $providedDependencies->get(BootstrapProviderInterface::class);
    }

    /**
     * @throws NoDependencyException
     * @throws CodeGenException
     */
    private function getInstanceProvider(ProvidedDependencies $providedDependencies): InstanceProvider
    {
        if (!$providedDependencies->has(InstanceProviderInterface::class)) {
            EventDispatcherLinkerException::throw(
                'You must execute %s before executing %s',
                DependencyInjectionGenerator::class,
                self::class
            );
        }

        $instanceProvider = $providedDependencies->get(InstanceProviderInterface::class);
        if (!$instanceProvider instanceof InstanceProvider) {
            EventDispatcherLinkerException::throw(
                'There is implementation %s of %s in provided dependencies, '
                . 'but %s is compatible only with %s provided by %s',
                $instanceProvider::class,
                InstanceProviderInterface::class,
                self::class,
                InstanceProvider::class,
                DependencyInjectionGenerator::class
            );
        }
        return $instanceProvider;
    }

    private function generateConfigCode(array $userConfig): void
    {
        $this->corsProvider->addCode('$castedEvent = instance_cast($event, \Kaa\HttpKernel\Event\ResponseEvent::class);
        if ($castedEvent === null) {
            return;
        }');
        $this->corsProvider->addCode('$req = $castedEvent->getRequest();');
        $this->corsProvider->addCode('$resp = $castedEvent->getResponse();');
        $nums = 0;
        foreach ($userConfig['pvpender_cors']["paths"] as $path => $headers) {
            if ($nums === 0) {
                $this->corsProvider->addCode(sprintf(
                    'if (preg_match("/%s/", $req->getRoute())){',
                    addcslashes($path, "/")
                ));
            } else {
                $this->corsProvider->addCode(sprintf(
                    'elseif (preg_match("/%s/", $req->getRoute())){',
                    addcslashes($path, "/")
                ));
            }
            $this->corsProvider->addCode('$mas = [');
            foreach ($headers as $key => $value) {
                $this->corsProvider->addCode(sprintf(
                    "'%s' => '%s',",
                    $key,
                    is_array($value) ? implode(', ', $value) : (string)$value
                ));
            }
            $this->corsProvider->addCode('];');
            $this->corsProvider->addCode(sprintf(
                '$resp->setHeader($mas);'
            ));
            $this->corsProvider->addCode("}");
            $nums++;
        }
        if ($nums > 0) {
            $this->corsProvider->addCode('else {');
            $this->corsProvider->addCode("}");
        }
    }
}
