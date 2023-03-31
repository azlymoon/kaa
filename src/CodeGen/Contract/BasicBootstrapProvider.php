<?php

declare(strict_types=1);

namespace Kaa\CodeGen\Contract;

use Kaa\CodeGen\DumpableInterface;
use Symfony\Component\Filesystem\Filesystem;

class BasicBootstrapProvider implements BoostrapProviderInterface, DumpableInterface
{
    /**
     * @var string[]
     */
    private array $code = [];

    private readonly string $directory;

    /**
     * @param mixed[] $userConfig
     */
    public function __construct(
        array $userConfig,
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
        $this->directory = rtrim($userConfig['code_gen_directory'], '/') . '/Bootstrap';
    }

    public function addCode(string $code): void
    {
        $this->code[] = $code;
    }

    public function getCallBootstrapCode(): string
    {
        return sprintf('require \'%s/%s\'', $this->directory, 'bootstrap.php');
    }

    public function dump(): void
    {
        if (!$this->filesystem->exists($this->directory)) {
            $this->filesystem->mkdir($this->directory);
        }

        $code = "<?php \n\n" . implode("\n\n", $this->code);
        file_put_contents($this->directory . '/bootstrap.php', $code);
    }
}
