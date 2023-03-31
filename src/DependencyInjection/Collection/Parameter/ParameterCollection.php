<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection\Parameter;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\DependencyInjection\Exception\BadDefinitionException;

#[PhpOnly]
class ParameterCollection
{
    /** @var Parameter[] */
    private array $parameters = [];

    /** @var Parameter[] */
    private array $bindedParameters = [];

    /**
     * @throws CodeGenException
     */
    public function add(Parameter $parameter): self
    {
        if (array_key_exists($parameter->name, $this->parameters)) {
            BadDefinitionException::throw(
                'Parameter %s already exists',
                $parameter->name
            );
        }

        $this->parameters[$parameter->name] = $parameter;

        if ($parameter->binding === null) {
            return $this;
        }

        if (array_key_exists($parameter->binding, $this->bindedParameters)) {
            BadDefinitionException::throw(
                'Binding %s is already binded to parameter %s',
                $parameter->binding,
                $this->bindedParameters[$parameter->binding]->name
            );
        }

        $this->bindedParameters[$parameter->binding] = $parameter;

        return $this;
    }

    public function have(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    public function get(string $name): Parameter
    {
        return $this->parameters[$name];
    }

    public function haveBinding(string $type, string $name): bool
    {
        $binding = sprintf('%s $%s', $type, $name);

        return array_key_exists($binding, $this->bindedParameters);
    }

    public function getByBinding(string $type, string $name): Parameter
    {
        $binding = sprintf('%s $%s', $type, $name);

        return $this->bindedParameters[$binding];
    }
}
