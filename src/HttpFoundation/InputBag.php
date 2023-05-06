<?php

namespace Kaa\HttpFoundation;

use InvalidArgumentException;
use Kaa\HttpFoundation\Exception\BadRequestException;

/**
 * InputBag is a container for user input values such as $_GET, $_POST, $_REQUEST, and $_COOKIE.
 */
class InputBag
{
    /**
     * Parameter storage.
     * @var ?mixed[] $parameters
     */
    private $parameters;

    /** @param ?mixed[] $parameters */
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
            throw new InvalidArgumentException(sprintf('Expected a scalar value as a 2nd argument to "%s()",
              "%s" given.', __METHOD__, gettype($default)));
        }

        if ($this->parameters !== null && \array_key_exists($key, $this->parameters)) {
            $value = $this->parameters[$key];
        } else {
            $value = $default;
        }

        if ($value !== null && $this !== $value && !\is_scalar($value)) {
            throw new BadRequestException(sprintf('Input value "%s" contains a non-scalar value.', $key));
        }

        return $value;
    }

//    /**
//     * Replaces the current input values by a new set.
//     */
//    public function replace(array $inputs = [])
//    {
//        $this->parameters = [];
//        $this->add($inputs);
//    }
//
//    /**
//     * Adds input values.
//     */
//    public function add(array $inputs = [])
//    {
//        foreach ($inputs as $input => $value) {
//            $this->set($input, $value);
//        }
//    }

    /**
     * Sets an input by name.
     *
     * @param ?mixed[] $value
     * @throws InvalidArgumentException
     */
    public function set(string $key, $value): void
    {
        if ($value !== null && !\is_scalar($value) && !\is_array($value)) {
            throw new InvalidArgumentException(sprintf('Expected a scalar, or an array as a 2nd argument to "%s()",
            "%s" given.', __METHOD__, gettype($value)));
        }
        $this->parameters[$key] = $value;
    }

//    public function filter(string $key, mixed $default = null, int $filter = \FILTER_DEFAULT, mixed $options = []): mixed
//    {
//        $value = $this->has($key) ? $this->all()[$key] : $default;
//
//        // Always turn $options into an array - this allows filter_var option shortcuts.
//        if (!\is_array($options) && $options) {
//            $options = ['flags' => $options];
//        }
//
//        if (\is_array($value) && !(($options['flags'] ?? 0) & (\FILTER_REQUIRE_ARRAY | \FILTER_FORCE_ARRAY))) {
//            throw new BadRequestException(sprintf('Input value "%s" contains an array, but "FILTER_REQUIRE_ARRAY" or "FILTER_FORCE_ARRAY" flags were not set.', $key));
//        }
//
//        if ((\FILTER_CALLBACK & $filter) && !(($options['options'] ?? null) instanceof \Closure)) {
//            throw new \InvalidArgumentException(sprintf('A Closure must be passed to "%s()" when FILTER_CALLBACK is used, "%s" given.', __METHOD__, get_debug_type($options['options'] ?? null)));
//        }
//
//        return filter_var($value, $filter, $options);
//    }

    // This methods from ParameterBag.php

    /**
     * Returns true if the parameter is defined.
     */

    /**
     * Returns the parameters.
     *
     * @param ?string $key The name of the parameter to return or null to get them all
     * @return mixed
     */
    public function all($key = null)
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