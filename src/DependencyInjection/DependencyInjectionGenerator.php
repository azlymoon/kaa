<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\Contract\ServiceStorageInterface;
use Kaa\CodeGen\GeneratorInterface;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\DependencyInjection\Collection\Service\ServiceCollection;
use Kaa\DependencyInjection\ConfigParser\ConfigParser;
use Kaa\DependencyInjection\ConfigParser\ConfigParserInterface;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\DependencyInjection\Contract\ServiceStorage;
use Kaa\DependencyInjection\ServiceFinder\ServiceFinder;
use Kaa\DependencyInjection\Validator\ContainerValidator;
use Kaa\DependencyInjection\Validator\ContainerValidatorInterface;

#[PhpOnly]
readonly class DependencyInjectionGenerator implements GeneratorInterface
{
    public function __construct(
        private ServiceFinder $serviceFinder = new ServiceFinder(),
        private ConfigParserInterface $configParser = new ConfigParser(),
        private ContainerValidatorInterface $containerValidator = new ContainerValidator(),
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

        $providedDependencies
            ->add(InstanceProviderInterface::class, new InstanceProvider($container, $userConfig))
            ->add(ServiceStorageInterface::class, new ServiceStorage($container));
    }
}
