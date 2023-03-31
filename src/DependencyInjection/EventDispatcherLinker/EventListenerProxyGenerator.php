<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\EventDispatcherLinker;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\EventDispatcher\EventInterface;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use ReflectionException;
use Symfony\Component\Filesystem\Filesystem;

#[PhpOnly]
readonly class EventListenerProxyGenerator
{
    private const CALL_CODE = <<<'PHP'
%listener%->%methodName%($event);
PHP;


    private const INSTANCE_CAST_CALL_CODE = <<<'PHP'
$castedEvent = instance_cast($event, \%type%::class);
if ($castedEvent === null) {
    return;
}

%listener%->%methodName%($castedEvent);
PHP;

    private string $directory;

    private string $namespace;

    private PhpFile $phpFile;

    private ClassType $class;

    /**
     * @param mixed[] $userConfig
     */
    public function __construct(
        array $userConfig,
        private Filesystem $filesystem = new Filesystem(),
    ) {
        $this->directory = rtrim($userConfig['code_gen_directory'], '/') . '/EventListenerLinker';
        $this->namespace = trim($userConfig['code_gen_namespace'], '\\') . '\\EventListenerLinker';

        $this->phpFile = new PhpFile();
        $phpNamespace = $this->phpFile->addNamespace($this->namespace);
        $this->class = $phpNamespace->addClass($this->getClassName());
    }

    /**
     * @throws ReflectionException
     * @throws CodeGenException
     */
    public function generateProxy(
        string $parameterType,
        string $serviceName,
        string $methodName,
        InstanceProvider $instanceProvider
    ): string {
        $proxyMethodName = 'p_' . sha1($serviceName . $methodName);
        $method = $this->class->addMethod($proxyMethodName);

        $method->setVisibility(ClassLike::VisibilityPublic);
        $method->setStatic();
        $method->setReturnType('void');

        $parameter = $method->addParameter('event');
        $parameter->setType(EventInterface::class);

        $replacements = [
            '%listener%' => $instanceProvider->provideInstanceCode($serviceName),
            '%methodName%' => $methodName,
            '%type%' => $parameterType,
        ];

        $template = $parameterType === EventInterface::class ? self::CALL_CODE : self::INSTANCE_CAST_CALL_CODE;

        $method->addBody(strtr($template, $replacements));

        return sprintf('[%s::class, \'%s\']', $this->getFqnClassName(), $proxyMethodName);
    }

    private function getFqnClassName(): string
    {
        return $this->namespace . '\\' . $this->getClassName();
    }

    private function getClassName(): string
    {
        return 'EventListenerProxy';
    }

    public function dump(): void
    {
        if (!$this->filesystem->exists($this->directory)) {
            $this->filesystem->mkdir($this->directory);
        }

        $code = (new PsrPrinter())->printFile($this->phpFile);
        $fileName = $this->directory . DIRECTORY_SEPARATOR . $this->getClassName() . '.php';
        file_put_contents($fileName, $code);
    }
}
