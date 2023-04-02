<?php

declare(strict_types=1);

namespace Kaa\CodeGenKernel;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Config\PhpConfig;
use Kaa\CodeGen\Contract\BootstrapProviderInterface;
use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\Exception\InvalidDependencyException;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\GenerationManager;
use Kaa\CodeGen\GeneratorInterface;
use Kaa\CodeGenKernel\Exception\ConfigException;
use Kaa\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;

#[PhpOnly]
readonly class CodeGenKernel
{
    private const KERNEL_PROVIDER_CODE = <<<'PHP'
<?php

namespace KaaGenerated;

class KernelProvider {
    public static function getKernel(): \Kaa\HttpKernel\HttpKernel
    {
        %bootstrap%;
        return new \Kaa\HttpKernel\HttpKernel(%kernelDispatcher%);
    }
}
PHP;

    public function __construct(
        private string $kernelDir
    ) {
    }

    /**
     * @throws ConfigException
     * @throws InvalidDependencyException
     * @throws NoDependencyException
     */
    public function generate(): void
    {
        $userConfig = $this->parseConfig();
        $userConfig['kernel_dir'] = $this->kernelDir;
        $userConfig['code_gen_namespace'] = 'KaaGenerated';
        $userConfig['code_gen_directory'] = $this->kernelDir . DIRECTORY_SEPARATOR . 'generated';
        $userConfig['service']['kernel.dispatcher'] = [
            'class' => EventDispatcher::class
        ];

        $modulesPath = $this->kernelDir . DIRECTORY_SEPARATOR . '/config/modules.php';
        if (!file_exists($modulesPath)) {
            throw new ConfigException(sprintf('File %s does not exist', $modulesPath));
        }

        $modules = require $modulesPath;

        /** @var GeneratorInterface[] $moduleInstances */
        $moduleInstances = array_map(static fn(string $moduleName) => new $moduleName(), $modules);

        $generationManager = new GenerationManager(new PhpConfig($moduleInstances, $userConfig));

        $dependencies = $generationManager->generate();
        if (!$dependencies->has(InstanceProviderInterface::class)) {
            throw new ConfigException('No module provided ' . InstanceProviderInterface::class);
        }

        $instanceProvider = $dependencies->get(InstanceProviderInterface::class);
        $boostrapProvider = $dependencies->get(BootstrapProviderInterface::class);

        $replacements = [
            '%kernelDispatcher%' => $instanceProvider->provideInstanceCode('kernel.dispatcher'),
            '%bootstrap%' => $boostrapProvider->getCallBootstrapCode(),
        ];

        $path = $this->kernelDir . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . 'KernelProvider.php';
        file_put_contents($path, strtr(self::KERNEL_PROVIDER_CODE, $replacements));
    }

    /**
     * @return mixed[]
     * @throws ConfigException
     */
    private function parseConfig(): array
    {
        $files = $this->scanDir($this->kernelDir . DIRECTORY_SEPARATOR . 'config');

        $yamlFiles = array_filter(
            $files,
            static fn(string $fileName) => pathinfo($fileName, PATHINFO_EXTENSION) === 'yaml'
        );

        $configArrays = array_map(Yaml::parseFile(...), $yamlFiles);
        return array_merge_recursive(...$configArrays);
    }

    /**
     * @return string[]
     * @throws ConfigException
     */
    private function scanDir(string $dir): array
    {
        if (!is_dir($dir)) {
            throw new ConfigException($dir . ' is not a directory');
        }

        $files = [];

        /** @var string[] $scanned */
        $scanned = scandir($dir);
        foreach ($scanned as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (is_dir($entry)) {
                $files = [...$files, ...$this->scanDir($entry)];
                continue;
            }

            $files[] = $entry;
        }

        return array_map(
            static fn(string $file) => $dir . DIRECTORY_SEPARATOR . $file,
            $files
        );
    }
}
