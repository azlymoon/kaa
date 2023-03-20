<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\NewInstanceGeneratorInterface;
use Kaa\CodeGen\Exception\InvalidDependencyException;
use Kaa\CodeGen\GeneratorInterface;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\DependencyInjection\Contract\NewInstanceGenerator;
use Kaa\DependencyInjection\ServiceFinder\ClassServiceFinder;
use Kaa\DependencyInjection\ServiceFinder\ServiceFinderInterface;

#[PhpOnly]
readonly class DependencyInjectionGenerator implements GeneratorInterface
{
    /**
     * @param ServiceFinderInterface[] $serviceFinders
     */
    public function __construct(
        public array $serviceFinders = [new ClassServiceFinder()],
    ) {
    }

    /**
     * @param mixed[] $userConfig
     * @throws Exception
     */
    public function generate(array $userConfig, ProvidedDependencies $providedDependencies): void
    {
        $services = $this->findServices($userConfig);
        $serviceCollection = $this->buildServiceCollection($services);
        $this->validate($serviceCollection, $services);

        $providedDependencies->add(
            NewInstanceGeneratorInterface::class,
            new NewInstanceGenerator($serviceCollection, $userConfig)
        );
    }

    /**
     * @param mixed[] $userConfig
     * @return ServiceDefinition[]
     * @throws Exception
     */
    private function findServices(array $userConfig): array
    {
        $services = [];
        foreach ($this->serviceFinders as $serviceFinder) {
            $services[] = $serviceFinder->findServices($userConfig);
        }

        return array_merge(...$services);
    }

    /**
     * @param ServiceDefinition[] $services
     */
    private function buildServiceCollection(array $services): NamedServiceCollection
    {
        $serviceCollection = new NamedServiceCollection();
        foreach ($services as $service) {
            foreach ($service->aliases as $alias) {
                $serviceCollection->add($alias, $service);
            }
        }

        return $serviceCollection;
    }

    /**
     * @param ServiceDefinition[] $services
     * @throws InvalidDependencyException
     */
    private function validate(NamedServiceCollection $serviceCollection, array $services): void
    {
        $noImplementationsMessage = 'Service %s requires service %s for which no implementations are defined';
        $multipleImplementationsMessage
            = 'Service %s requires service %s for which exists multiple possible implementations: %s';

        $errors = [];
        foreach ($services as $service) {
            foreach ($service->dependencies as $dependency) {
                if (!$serviceCollection->has($dependency)) {
                    $errors[] = sprintf(
                        $noImplementationsMessage,
                        $service->type,
                        $dependency
                    );
                    continue;
                }

                if ($serviceCollection->hasMany($dependency)) {
                    $possibleImplementations = array_map(
                        static fn(ServiceDefinition $serviceDefinition) => $serviceDefinition->type,
                        $serviceCollection->getAll($dependency)
                    );

                    $errors[] = sprintf(
                        $multipleImplementationsMessage,
                        $service->type,
                        $dependency,
                        implode(', ', $possibleImplementations)
                    );
                }
            }
        }

        if (empty($errors)) {
            return;
        }

        throw new InvalidDependencyException(implode("\n", $errors));
    }
}
