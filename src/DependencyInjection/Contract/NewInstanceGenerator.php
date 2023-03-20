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
        if (!isset($this->availableMethods[$methodName])) {
            $this->generateMethod($className, $methodName);
        }
    }

    private function aliasToMethodName(string $alias): string
    {
        $typeName = $this->serviceCollection->getOne($alias)->type;
        return 'a' . sha1($typeName);
    }

    private function generateMethod(string $className, string $methodName): void
    {
        $this->makeClassIfNotExits();

        $phpFile = PhpFile::fromCode((string) file_get_contents($this->fileName()));
        $classes = $phpFile->getClasses();

        $class = reset($classes);
        assert($class instanceof ClassType);

        $property = $class->addProperty($methodName, 'null');
        $property->setStatic();
        $property->setVisibility(ClassLike::VisibilityPrivate);
        $property->setType($className);
        $property->setNullable();

        $method = $class->addMethod($methodName);
        $method->setStatic();
        $method->setVisibility(ClassLike::VisibilityPublic);
        $method->setReturnType($className);

        $method->addBody($this->generateMethodBody($className, $methodName));

        $this->saveFile($phpFile);
    }

    private function makeClassIfNotExits(): void
    {
        if ($this->filesystem->exists($this->fileName())) {
            return;
        }

        $phpFile = new PhpFile();
        $namespace = $phpFile->addNamespace($this->containerNamespace);
        $namespace->addClass(self::CONTAINER_CLASS_NAME);

        $this->saveFile($phpFile);
    }

    private function fileName(): string
    {
        return $this->containerDirectory . self::CONTAINER_CLASS_NAME . '.php';
    }

    private function saveFile(PhpFile $phpFile): void
    {
        if (!$this->filesystem->exists($this->containerDirectory)) {
            $this->filesystem->mkdir($this->containerDirectory);
        }

        $code = (new PsrPrinter())->printFile($phpFile);
        file_put_contents($this->fileName(), $code);
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
}
