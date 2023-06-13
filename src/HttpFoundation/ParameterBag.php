<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kaa\HttpFoundation;

/**
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * ParameterBag is a container for key/value pairs.
 *
 * @author Fabien Potencier <fabien@symfony.com>
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

    /**
     * Returns the parameters.
     *
     * @param ?string $key The name of the parameter to return or null to get them all
     * @return string|string[]
     */
    public function all(?string $key = null)
    {
        if ($key === null) {
            return $this->parameters;
        }

        return $this->parameters[$key] ?? [];
    }

    /**
     * Returns the parameter keys.
     * @return string[]
     */
    public function keys()
    {
        return array_map('strval', array_keys($this->parameters));
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

    /** @param boolean|string $value */
    public function set(string $key, $value): void
    {
        $this->parameters[$key] = (string)$value;
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
        return (string)preg_replace('/[^[:alpha:]]/', '', (string)$this->get($key, $default));
    }

    /**
     * Returns the alphabetic characters and digits of the parameter value.
     */
    public function getAlnum(string $key, string $default = ''): string
    {
        return (string)preg_replace('/[^[:alnum:]]/', '', (string)$this->get($key, $default));
    }

    /**
     * Returns the digits of the parameter value.
     */
    public function getDigits(string $key): string
    {
        if (!isset($this->parameters[$key])) {
            return '';
        }

        return (string)preg_replace('/\D/', '', $this->parameters[$key]);
    }

    /**
     * Returns the parameter value converted to integer.
     */
    public function getInt(string $key, int $default = 0): int
    {
        return (int)$this->get($key, (string)$default);
    }

    /**
     * Returns the parameter value converted to boolean.
     */
    public function getBoolean(string $key, bool $default = false): bool
    {
        if (isset($this->parameters[$key])) {
            $value = strtolower($this->parameters[$key]);
            return $value === 'true' || $value === 'on' || $value === '1' || $value === 'yes';
        }

        return $default;
    }
}

// TODO: [] сделать тесты для ParameterBag
// TODO: [] тест getAll показывает, что в parameters должен быть тип (?string[])[],
//          но это нигде не использовалось в Request, так что не понятно зачем так сделано,
//          я пока это пропустил
// TODO: [] не работает метод getIteror, в KPHP тупо нет глобального класса \IteratorAggregate, \Countable
// TODO: [] не работает метод filter(), в KPHP нет функции filter_var()
//          - [+] реализовать метод getDigits() без filter()
//          - [+] реализовать метод getBoolean() без filter() - нужно для Николая
