<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection;

use ArrayIterator;
use IteratorAggregate;
use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Attribute\Factory;
use Kaa\DependencyInjection\Attribute\When;
use Kaa\DependencyInjection\Exception\BadDefinitionException;
use Kaa\DependencyInjection\Exception\DoubleFactoryConfigurationException;
use Traversable;

#[PhpOnly]
class FactoryCollection implements IteratorAggregate
{
    /** @var Factory[] */
    private array $factories = [];

    /**
     * @param Factory[] $factories
     * @throws CodeGenException
     */
    public function __construct(array $factories)
    {
        foreach ($factories as $factory) {
            $this->add($factory);
        }
    }

    /**
     * @throws CodeGenException
     */
    public function add(Factory $factory): void
    {
        $environments = (array)$factory->when;
        foreach ($environments as $environment) {
            if (isset($this->factories[$environment])) {
                BadDefinitionException::throw(
                    'Factories %s and %s were both configured to create one service for environment %s',
                    $this->factories[$environment]->factory,
                    $factory->factory
                );
            }

            $this->factories[$environment] = $factory;
        }
    }

    /**
     * @return Traversable<Factory>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->factories);
    }

    public function hasEnvironmentFactories(): bool
    {
        return !empty($this->factories)
            && !(count($this->factories) === 1 && isset($this->factories[When::DEFAULT_ENVIRONMENT]));
    }

    public function notEmpty(): bool
    {
        return !$this->empty();
    }

    public function empty(): bool
    {
        return empty($this->factories);
    }
}
