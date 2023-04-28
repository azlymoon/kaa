<?php

namespace Kaa\HttpFoundation;

use Kaa\HttpFoundation\Request;

/**
 * Реализован полный функционал, за исключением функции filter()
 * Т.к. в KPHP нет функции filter_var(), нужно попробовать прикрутить наш валидатор
 *
 * ParameterBag is a container for key/value pairs.
 */
class ParameterBag
{
    /**
     * Parameter storage.
     * @var string[] $parameters
     */
    protected $parameters;

    /** @param string[] $parameters */
    public function __construct($parameters = [])
    {
        $this->parameters = $parameters;
    }

    // TODO add BadRequestException
    /**
     * Returns the parameters.
     *
     * @param ?string $key The name of the parameter to return or null to get them all
     * @return string|string[]
     */
    public function all($key = null)
    {
        if ($key === null) {
            return $this->parameters;
        }

        return $this->parameters[$key] ?? [];
    }

    /**
     * Returns the parameter keys.
     */
    public function keys(): array
    {
        return array_keys($this->parameters);
    }

    /**
     * @param string[] $parameters
     * Replaces the current parameters by a new set.
     */
    public function replace($parameters = []): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string[] $parameters
     * Adds parameters.
     */
    public function add($parameters = []): void
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        if (\array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }
        return $default;
    }

    public function set(string $key, string $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Returns true if the parameter is defined.
     */
    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->parameters);
    }

    /**
     * Removes a parameter.
     */
    public function remove(string $key): void
    {
        unset($this->parameters[$key]);
    }

    /**
     * Returns the alphabetic characters of the parameter value.
     */
    public function getAlpha(string $key, string $default = ''): string
    {
        return (string)preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the alphabetic characters and digits of the parameter value.
     */
    public function getAlnum(string $key, string $default = ''): string
    {
        return (string)preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the parameter value converted to integer.
     */
    public function getInt(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    /**
     * Returns an iterator for headers.
     */
    public function getIterator(): array
    {
        $iterator = [];
        foreach ($this->parameters as $name => $values) {
            $iterator[$name] = $values;
        }
        return $iterator;
    }

    /**
     * Returns the number of parameters.
     */
    public function count(): int
    {
        return \count($this->parameters);
    }
}
