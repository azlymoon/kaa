<?php

namespace Kaa\HttpFoundation;

//use DateTime;

/**
 * Компонент полностью реализован в соответствии с HttpFoundation
 *
 * HeaderBag is a container for HTTP headers.
 */
class HeaderBag
{
    protected const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const LOWER = '-abcdefghijklmnopqrstuvwxyz';

    # Set() shows, that headers can be string|string[]|array[string[]], so its mixed
    /** @var mixed $headers */
    protected $headers;

    /** @var string[] $cacheControl */
    protected $cacheControl = [];

    /** @param mixed $headers */
    public function __construct($headers = [])
    {
        foreach ($headers as $key => $values) {
            $this->set($key, $values);
        }
    }

//    /**
//     * Returns the headers as a string.
//     */
//    public function __toString(): string
//    {
//        $headers = $this->all();
//        if ($headers == []) {
//            return '';
//        }
//
//        ksort($headers);
//        $max = max(array_map(static function ($key) {
//            if (is_string($key)) {
//                return strlen($key);
//            }
//            return 0;
//        }, array_keys($headers))) + 1;
//
//        $content = '';
//        foreach ($headers as $name => $values) {
//            $name = strtolower($name);
//            $delimiters = ['-'];
//            foreach ($delimiters as $delimiter) {
//                $words = explode($delimiter, $name);
//                $newWords = array();
//                foreach ($words as $word) {
//                    $newWords[] = ucfirst($word);
//                }
//                $name = implode($delimiter, $newWords);
//            }
//            if (is_array($values)) {
//                foreach ($values as $value) {
//                    $content .= sprintf("%-{$max}s %s\r\n", $name . ':', $value);
//                }
//            }
//        }
//
//        return $content;
//    }

    /**
     * Returns the headers.
     *
     * @param ?string $key The name of the headers to return or null to get them all
     * @return mixed
     */
    public function all(?string $key = null)
    {
        if ($key !== null) {
            return $this->headers[strtr($key, self::UPPER, self::LOWER)] ?? [];
        }

        return $this->headers;
    }

//    /**
//     * Returns the parameter keys.
//     *
//     * @return string[]|int[]
//     */
//    public function keys()
//    {
//        return array_keys($this->all());
//    }
//
//    /**
//     * Replaces the current HTTP headers by a new set.
//     */
//    public function replace(array $headers = [])
//    {
//        $this->headers = [];
//        $this->add($headers);
//    }
//
//    /**
//     * Adds new headers the current HTTP headers set.
//     */
//    public function add(array $headers)
//    {
//        foreach ($headers as $key => $values) {
//            $this->set($key, $values);
//        }
//    }

    /**
     * Returns the first header by name or the default one.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $headers = $this->all($key);

        if (is_string($headers)) {
            return $headers;
        }

        if (count($headers) === 0) {
            return $default;
        }

        return (string)$headers[0];
    }

    /**
     * Sets a header by name.
     *
     * @param int|string           $key
     * @param mixed                $values  The value or an array of values
     * @param bool                 $replace Whether to replace the actual value or not (true by default)
     */
    public function set($key, $values, bool $replace = true): void
    {
        $key = strtr((string)$key, self::UPPER, self::LOWER);

        if (is_array($values)) {
            $values = array_values($values);

            if ($replace === true || !isset($this->headers[$key])) {
                $this->headers[$key] = $values;
            } else {
                $this->headers[$key] = array_merge($this->headers[$key], $values);
            }
        } else {
            if ($replace === true || !isset($this->headers[$key])) {
                $this->headers[$key] = [$values];
            } else {
                $this->headers[$key][] = $values;
            }
        }
//
//        if ('cache-control' === $key) {
//            $this->cacheControl = $this->parseCacheControl(implode(', ', $this->headers[$key]));
//        }
    }

//    /**
//     * Returns true if the HTTP header is defined.
//     */
//    public function has(string $key): bool
//    {
//        return \array_key_exists(strtr($key, self::UPPER, self::LOWER), $this->all());
//    }
//
//    /**
//     * Returns true if the given HTTP header contains the given value.
//     */
//    public function contains(string $key, string $value): bool
//    {
//        return \in_array($value, $this->all($key));
//    }
//
//    /**
//     * Removes a header.
//     */
//    public function remove(string $key): void
//    {
//        $key = strtr($key, self::UPPER, self::LOWER);
//
//        unset($this->headers[$key]);
//
//        if ('cache-control' === $key) {
//            $this->cacheControl = [];
//        }
//    }
//
//    /**
//     * Returns the HTTP header value converted to a date.
//     *
//     *
//     *
//     * @throws \RuntimeException When the HTTP header is not parseable
//     */
//    public function getDate(string $key, ?DateTime $default = null): DateTime|false|null
//    {
//        $value = $this->get($key);
//        if ($value === null) {
//            return $default;
//        }
//
//        # The KPHP create from format function returns null|\DateTime
//        $date = DateTime::createFromFormat(\DATE_RFC2822, $value);
//        if (!isset($date)) {
//            throw new \RuntimeException(sprintf('The "%s" HTTP header is not parseable (%s).', $key, $value));
//        }
//
//        return $date;
//    }
//
//    /**
//     * Adds a custom Cache-Control directive.
//     * @param true|string $value
//     */
//    public function addCacheControlDirective(string $key, $value = true): void
//    {
//        $this->cacheControl[$key] = (string)$value;
//
//        $this->set('Cache-Control', $this->getCacheControlHeader());
//    }

    /**
     * Returns true if the Cache-Control directive is defined.
     */
    public function hasCacheControlDirective(string $key): bool
    {
        return \array_key_exists($key, $this->cacheControl);
    }

//    /**
//     * Returns a Cache-Control directive value by name.
//     *
//     * @return true|string|null
//     */
//    public function getCacheControlDirective(string $key)
//    {
//        $value = $this->cacheControl[$key] ?? null;
//        if (true === $value) {
//            return true;
//        } elseif (isset($value)) {
//            return (string)$this->cacheControl[$key];
//        } else {
//            return null;
//        }
//    }
//
//    /**
//     * Removes a Cache-Control directive.
//     */
//    public function removeCacheControlDirective(string $key)
//    {
//        unset($this->cacheControl[$key]);
//
//        $this->set('Cache-Control', $this->getCacheControlHeader());
//    }
//
//    /**
//     * Returns an iterator for headers.
//     */
//    public function getIterator(): array
//    {
//        $iterator = [];
//        foreach ($this->headers as $name => $values) {
//            $iterator[$name] = $values;
//        }
//        return $iterator;
//    }
//
//
//    /**
//     * Returns the number of headers.
//     */
//    public function count(): int
//    {
//        return \count($this->headers);
//    }
//
//    protected function getCacheControlHeader(): string
//    {
//        ksort($this->cacheControl);
//
//        return HeaderUtils::toString($this->cacheControl, ',');
//    }
//
//    /**
//     * Parses a Cache-Control HTTP header.
//     */
//    protected function parseCacheControl(string $header): array
//    {
//        $parts = HeaderUtils::split($header, ',=');
//
//        return HeaderUtils::combine($parts);
//    }
}


// TODO [+] Start TODO for HeaderBag.php
// TODO [] Change licence section
// TODO [+] Figure out what is stored in headers and assign the correct data types to set(), get() methods
