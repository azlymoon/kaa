<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Contract;

use Kaa\CodeGen\Attribute\PhpOnly;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Symfony\Component\Filesystem\Filesystem;

#[PhpOnly]
class DiContainerGenerator
{
    private readonly string $directory;

    private readonly PhpFile $phpFile;

    private readonly ClassType $class;

    /** @var string[] */
    private array $methods = [];

    /**
     * @param mixed[] $userConfig
     */
    public function __construct(
        array $userConfig,
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
        $this->directory = rtrim($userConfig['code_gen_directory'], '/') . '/DependencyInjection';
        $namespace = rtrim($userConfig['code_gen_namespace'], '\\') . '\\DependencyInjection';

        $this->phpFile = new PhpFile();
        $namespace = $this->phpFile->addNamespace($namespace);
        $this->class = $namespace->addClass($this->getClassName());
    }

    public function getClassName(): string
    {
        return 'Kaa_DiContainer';
    }

    public function hasMethod(string $methodName): bool
    {
        return in_array($methodName, $this->methods, true);
    }

    public function addVar(string $type, string $name): void
    {
        $var = $this->class->addProperty($name);
        if ($type !== 'mixed') {
            $var->setNullable();
        }

        $var->setType($type);
        $var->setStatic();
        $var->setVisibility(ClassLike::VisibilityPrivate);
    }

    public function addMethod(string $type, string $name, string $code): void
    {
        $method = $this->class->addMethod($name);
        $method->setReturnType($type);
        $method->setStatic();
        $method->setVisibility(ClassLike::VisibilityPublic);
        $method->setBody($code);

        $this->methods[] = $name;
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
