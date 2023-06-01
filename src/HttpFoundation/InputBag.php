<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kaa\HttpFoundation;

use InvalidArgumentException;
use Kaa\HttpFoundation\Exception\BadRequestException;

/**
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * InputBag is a container for user input values such as $_GET, $_POST, $_REQUEST, and $_COOKIE.
 *
 * @author Saif Eddin Gmati <azjezz@protonmail.com>
 */
class InputBag
{
    /**
     * Parameter storage.
     * @var ?mixed $parameters
     */
    private $parameters;

    /** @param ?mixed $parameters */
    public function __construct($parameters = null)
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns a scalar input value by name.
     *
     * @param ?mixed $default The default value if the input key does not exist
     * @return mixed
     * @throws InvalidArgumentException
     * @throws BadRequestException
     */
    public function get(string $key, $default = null)
    {
        if ($default !== null && !\is_scalar($default)) {
            throw new InvalidArgumentException(sprintf(
                'Expected a scalar value as a 2nd argument to "%s()", "%s" given.',
                __METHOD__,
                gettype($default)
            ));
        }

        if ($this->parameters !== null && \array_key_exists($key, $this->parameters)) {
            $value = $this->parameters[$key];
        } else {
            $value = $default;
        }

        if ($value !== null && !\is_scalar($value)) {
            throw new BadRequestException(sprintf('Input value "%s" contains a non-scalar value.', $key));
        }

        return $value;
    }

    /**
     * Sets an input by name.
     *
     * @param ?mixed $value
     * @throws InvalidArgumentException
     */
    public function set(string $key, $value): void
    {
        if ($value !== null && !\is_scalar($value) && !\is_array($value)) {
            throw new InvalidArgumentException(sprintf(
                'Expected a scalar, or an array as a 2nd argument to "%s()", "%s" given.',
                __METHOD__,
                gettype($value)
            ));
        }
        $this->parameters[$key] = $value;
    }

    // Methods from ParameterBag.php

    /**
     * Returns true if the parameter is defined.
     */

    /**
     * Returns the parameters.
     *
     * @param ?string $key The name of the parameter to return or null to get them all
     * @return mixed
     */
    public function all(?string $key = null)
    {
        if ($key === null) {
            return $this->parameters;
        }

        return $this->parameters[$key] ?? [];
    }

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
}

//TODO: [+] Add exception handling for InputBag
//TODO: [-] Add a check that the object passed to InputBag->paramaters implements the \Stringable interface
//          - Unfortunately, KPHP does not allow storing objects in arrays without a known type
