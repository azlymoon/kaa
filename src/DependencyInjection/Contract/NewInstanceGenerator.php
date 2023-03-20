<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Contract;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\NewInstanceGeneratorInterface;
use Kaa\DependencyInjection\NamedServiceCollection;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Symfony\Component\Filesystem\Filesystem;

#[PhpOnly]
class NewInstanceGenerator implements NewInstanceGeneratorInterface
{
    private const CONTAINER_CLASS_NAME = 'Container';

    /** @var string[] */
    private array $availableMethods = [];

    private readonly string $containerNamespace;

    private readonly string $containerDirectory;

    private ?PhpFile $phpFile = null;

    /**
     * @param mixed[] $userConfig
     */
    public function __construct(
        private readonly NamedServiceCollection $serviceCollection,
        array $userConfig,
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
        $this->containerNamespace = rtrim($userConfig['code_gen_namespace'], '\\') . '\\DependencyInjection';
        $this->containerDirectory = rtrim($userConfig['code_gen_directory'], '/') . '/DependencyInjection';
    }

    public function getNewInstanceCode(string $varName, string $className): string
    {
        $this->generateMethodIfNotExists($className);

        return sprintf(
            '$%s = %s\%s::%s();',
            $varName,
            $this->containerNamespace,
            self::CONTAINER_CLASS_NAME,
            $this->aliasToMethodName($className)
        );
    }

    private function generateMethodIfNotExists(string $className): void
    {
        $methodName = $this->aliasToMethodName($className);
        if (in_array($methodName, $this->availableMethods, true)) {
            return;
        }

        $this->generateMethod($className, $methodName);
    }

    private function aliasToMethodName(string $alias): string
    {
        $typeName = $this->serviceCollection->getOne($alias)->type;
        return 'a' . sha1($typeName);
    }

    private function generateMethod(string $className, string $methodName): void
    {
        $class = $this->getClass();

        $property = $class->addProperty($methodName, null);
        $property->setStatic();
        $property->setVisibility(ClassLike::VisibilityPrivate);
        $property->setType($className);
        $property->setNullable();

        $method = $class->addMethod($methodName);
        $method->setStatic();
        $method->setVisibility(ClassLike::VisibilityPublic);
        $method->setReturnType($className);

        $method->addBody($this->generateMethodBody($className, $methodName));

        $this->availableMethods[] = $methodName;
    }

    private function getClass(): ClassType
    {
        if ($this->phpFile === null) {
            $this->phpFile = new PhpFile();
            $namespace = $this->phpFile->addNamespace($this->containerNamespace);
            $namespace->addClass(self::CONTAINER_CLASS_NAME);
        }

        $classes = $this->phpFile->getClasses();

        /** @var ClassType $class */
        $class = reset($classes);
        return $class;
    }

    private function generateMethodBody(string $className, string $methodName): string
    {
        $body = [];

        $returnIfCreatedCode = <<<'PHP'
if (self::$%s !== null) {
    return self::$%s;
}
PHP;

        $body[] = sprintf($returnIfCreatedCode, $methodName, $methodName);

        $service = $this->serviceCollection->getOne($className);
        $arguments = [];
        foreach ($service->dependencies as $dependency) {
            $this->generateMethodIfNotExists($dependency);
            $arguments[] = sprintf('self::%s()', $this->aliasToMethodName($dependency));
        }

        $setNewCode = <<<'PHP'
self::$%s = new %s(
    %s
);
PHP;
        $body[] = sprintf(
            $setNewCode,
            $methodName,
            $service->type,
            implode(",\n\t", $arguments)
        );

        $body[] = sprintf('return self::$%s;', $methodName);

        return implode("\n", $body);
    }

    public function dump(): void
    {
        if ($this->phpFile === null) {
            return;
        }

        if (!$this->filesystem->exists($this->containerDirectory)) {
            $this->filesystem->mkdir($this->containerDirectory);
        }

        $code = (new PsrPrinter())->printFile($this->phpFile);
        file_put_contents($this->containerDirectory . '/' . self::CONTAINER_CLASS_NAME . '.php', $code);
    }
}
