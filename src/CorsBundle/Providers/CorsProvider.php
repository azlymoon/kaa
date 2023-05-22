<?php

declare(strict_types=1);

namespace Kaa\CorsBundle\Providers;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\BootstrapProviderInterface;
use Kaa\CodeGen\DumpableInterface;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Symfony\Component\Filesystem\Filesystem;

#[PhpOnly]
class CorsProvider implements CorsProviderInterface, DumpableInterface
{
    private const METHOD_NAME = 'addHeaders';

    private readonly string $directory;

    private readonly string $namespace;

    private readonly PhpFile $phpFile;

    private readonly Method $method;

    /**
     * @param mixed[] $userConfig
     * @param Filesystem $filesystem
     */
    public function __construct(
        array $userConfig,
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
        $this->directory = rtrim($userConfig['code_gen_directory'], '/') . '/Cors';
        $this->namespace = trim($userConfig['code_gen_namespace'], '\\') . '\\Cors';

        $this->phpFile = new PhpFile();
        $phpNamespace = $this->phpFile->addNamespace($this->namespace);
        $class = $phpNamespace->addClass($this->getClassName());
        $this->method = $class->addMethod(self::METHOD_NAME);
        $this->method->addParameter("event", \Kaa\EventDispatcher\EventInterface::class);
        $this->method->setStatic();
        $this->method->setVisibility(ClassLike::VisibilityPublic);
        $this->method->setReturnType('void');
    }

    public function getFqnClassName(): string
    {
        return $this->namespace . '\\' . $this->getClassName();
    }

    public function getClassName(): string
    {
        return 'EventListenerCors';
    }

    public function addCode(string $code): void
    {
        $this->method->addBody($code);
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
