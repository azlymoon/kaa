<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\DumpableGeneratorInterface;
use Kaa\CodeGen\GeneratorInterface;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\DependencyInjection\Collection\Service\ServiceCollection;
use Kaa\DependencyInjection\ConfigParser\ConfigParser;
use Kaa\DependencyInjection\ConfigParser\ConfigParserInterface;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\DependencyInjection\ServiceFinder\ServiceFinder;
use Kaa\DependencyInjection\Validator\ContainerValidator;
use Kaa\DependencyInjection\Validator\ContainerValidatorInterface;

#[PhpOnly]
class DependencyInjectionGenerator implements GeneratorInterface, DumpableGeneratorInterface
{
    private ?InstanceProvider $generator = null;

    public function __construct(
        private readonly ServiceFinder $serviceFinder = new ServiceFinder(),
        private readonly ConfigParserInterface $configParser = new ConfigParser(),
        private readonly ContainerValidatorInterface $containerValidator = new ContainerValidator(),
    ) {
    }

    /**
     * @param mixed[] $userConfig
     * @throws Exception
     */
    public function generate(array $userConfig, ProvidedDependencies $providedDependencies): void
    {
        $services = $this->serviceFinder->findServices($userConfig);
        $serviceCollection = new ServiceCollection($services);

        $container = $this->configParser->parseConfig($userConfig, $serviceCollection);
        $this->containerValidator->validate($container);

        $this->generator = new InstanceProvider($container, $userConfig);
        $providedDependencies->add(
            InstanceProviderInterface::class,
            $this->generator
        );
    }

    public function dump(): void
    {
        $this->generator?->dump();
    }
}
