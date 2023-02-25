<?php

declare(strict_types=1);

namespace Kaa\Router\Interceptor;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class AvailableVars
{
    /**
     * @var AvailableVar[]
     */
    private array $availableVars = [];

    public function add(AvailableVar $availableVar): self
    {
        $this->availableVars[] = $availableVar;

        return $this;
    }

    public function getFirstByType(string $type): ?AvailableVar
    {
        $vars = $this->getByType($type);
        $var = reset($vars);
        return $var === false ? null : $var;
    }

    /**
     * @return AvailableVar[]
     */
    public function getByType(string $type): array
    {
        return array_filter($this->availableVars, static fn(AvailableVar $v) => $v->type === $type);
    }

    public function getFirstByName(string $name): ?AvailableVar
    {
        $vars = $this->getByName($name);
        $var = reset($vars);
        return $var === false ? null : $var;
    }

    /**
     * @return AvailableVar[]
     */
    public function getByName(string $name): array
    {
        return array_filter($this->availableVars, static fn(AvailableVar $v) => $v->name === $name);
    }

    public function have(string $name, string $type): bool
    {
        $vars = array_filter(
            $this->availableVars,
            static fn(AvailableVar $v) => $v->name === $name && $v->type === $type
        );

        return !empty($vars);
    }

    /**
     * @return AvailableVar[]
     */
    public function getAll(): array
    {
        return $this->availableVars;
    }
}
