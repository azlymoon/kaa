<?php

namespace Kaa\HttpFoundation;

/**
 * ResponseHeaderBag is a container for Response HTTP headers.
 */
class ResponseHeaderBag extends HeaderBag
{
    public const COOKIES_FLAT = 'flat';
    public const COOKIES_ARRAY = 'array';

    public const DISPOSITION_ATTACHMENT = 'attachment';
    public const DISPOSITION_INLINE = 'inline';

    /** @var mixed[] $computedCacheControl */
    protected $computedCacheControl = [];

    /** @var Cookie[][][] $cookies */
    protected $cookies = [];

    /** @var string[] $headerNames */
    protected $headerNames = [];

    /** @param string[][]|string[] $headers */
    public function __construct($headers = [])
    {
        parent::__construct($headers);

        if (!isset($this->headers['cache-control'])) {
            $this->setResponseHeader('Cache-Control', '');
        }

        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (!isset($this->headers['date'])) {
            $this->initDate();
        }
    }

    /**
     * Returns the headers, with original capitalizations.
     *
     * @return string[][]
     */
    public function allPreserveCase()
    {
        /** @var string[][] $headers */
        $headers = [];
        foreach ($this->all() as $name => $value) {
            $headers[$this->headerNames[$name] ?? $name] = array_map('strval', $value);
        }

        return $headers;
    }

    /** @return string[][] */
    public function allPreserveCaseWithoutCookies()
    {
        $headers = $this->allPreserveCase();
        if (isset($this->headerNames['set-cookie'])) {
            unset($headers[$this->headerNames['set-cookie']]);
        }

        return $headers;
    }

//    public function replace(array $headers = []): void
//    {
//        $this->headerNames = [];
//
//        parent::replace($headers);
//
//        if (!isset($this->headers['cache-control'])) {
//            $this->set('Cache-Control', '');
//        }
//
//        if (!isset($this->headers['date'])) {
//            $this->initDate();
//        }
//    }
//
//    /** @return string[]|string[][] */
//    public function all(?string $key = null)
//    {
//        $headers = parent::all();
//
//        if ($key !== null) {
//            $key = strtr($key, self::UPPER, self::LOWER);
//
//            if ($key !== 'set-cookie') {
//                $result = isset($headers[$key]) ? $headers[$key] : [];
//            } else {
//                $result = array_map('strval', $this->getCookies());
//            }
//
//            return $result;
//        }
//
//        foreach ($this->getCookies() as $cookie) {
//            $headers['set-cookie'][] = (string) $cookie;
//        }
//
//        return $headers;
//    }

    //TODO: working with cookie
    /**
     * Sets a header by name.
     *
     * @param int|string           $key     In fact, it's always just a string
     * @param mixed                $values  The value or an array of values
     * @param bool                 $replace Whether to replace the actual value or not (true by default)
     */

    public function set($key, $values, bool $replace = true): void
    {
        $key = (string)$key;
        $uniqueKey = strtr($key, self::UPPER, self::LOWER);

//        if ($uniqueKey === 'set-cookie') {
//            if ($replace) {
//                $this->cookies = [];
//            }
//            foreach ((array) $values as $cookie) {
//                $this->setCookie(Cookie::fromString($cookie));
//            }
//            $this->headerNames[$uniqueKey] = $key;
//
//            return;
//        }

        $this->headerNames[$uniqueKey] = $key;

        parent::set($key, $values, $replace);

        // ensure the cache-control header has sensible defaults
        $computed = $this->computeCacheControlValue();
        if (
            \in_array($uniqueKey, ['cache-control', 'etag', 'last-modified', 'expires'], true)
            && $computed !== ''
        ) {
            $this->headers['cache-control'] = [$computed];
            $this->headerNames['cache-control'] = 'Cache-Control';
            $this->computedCacheControl = $this->parseCacheControl($computed);
        }
    }

    /**
     * Sets a header by name.
     *
     * @param mixed                $values  The value or an array of values
     * @param bool                 $replace Whether to replace the actual value or not (true by default)
     */
    public function setResponseHeader(string $key, $values, bool $replace = true): void
    {
        $uniqueKey = strtr($key, HeaderBag::UPPER, self::LOWER);

        if ($uniqueKey === 'set-cookie') {
            if ($replace) {
                $this->cookies = [];
            }

            return;
        }

        $this->headerNames[$uniqueKey] = $key;

        parent::set($key, $values, $replace);

        // ensure the cache-control header has sensible defaults
        $computed = $this->computeCacheControlValue();
        if (
            \in_array($uniqueKey, ['cache-control', 'etag', 'last-modified', 'expires'], true)
            && $computed !== ''
        ) {
            $this->headers['cache-control'] = [$computed];
            $this->headerNames['cache-control'] = 'Cache-Control';
            $computedCacheControlMixed = $this->parseCacheControl($computed);
            $this->computedCacheControl = array_map('strval', $computedCacheControlMixed);
        }
    }

    public function remove(string $key): void
    {
        $uniqueKey = strtr($key, self::UPPER, self::LOWER);
        unset($this->headerNames[$uniqueKey]);

        if ($uniqueKey === 'set-cookie') {
            $this->cookies = [];

            return;
        }

        parent::remove($key);

        if ($uniqueKey === 'cache-control') {
            $this->computedCacheControl = [];
        }

        if ($uniqueKey === 'date') {
            $this->initDate();
        }
    }

    public function hasCacheControlDirective(string $key): bool
    {
        return \array_key_exists($key, $this->computedCacheControl);
    }

    public function setCookie(Cookie $cookie): void
    {
        $this->cookies[(string)$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        $this->headerNames['set-cookie'] = 'Set-Cookie';
    }

    /**
     * Returns an array with all cookies.
     *
     * @return Cookie[]
     *
     * @throws \InvalidArgumentException When the $format is invalid
     */
    public function getCookies(string $format = self::COOKIES_FLAT)
    {
        if (!\in_array($format, [self::COOKIES_FLAT, self::COOKIES_ARRAY])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Format "%s" invalid (%s).',
                    $format,
                    implode(', ', [self::COOKIES_FLAT, self::COOKIES_ARRAY])
                )
            );
        }

//        if ($format === self::COOKIES_ARRAY) {
//            return $this->cookies;
//        }

        $flattenedCookies = [];
        foreach ($this->cookies as $path) {
            foreach ($path as $cookies) {
                foreach ($cookies as $cookie) {
                    $flattenedCookies[] = $cookie;
                }
            }
        }

        return $flattenedCookies;
    }

//    /**
//     * @see HeaderUtils::makeDisposition()
//     */
//    public function makeDisposition(string $disposition, string $filename, string $filenameFallback = ''): string
//    {
//        return HeaderUtils::makeDisposition($disposition, $filename, $filenameFallback);
//    }

    /**
     * Returns the calculated value of the cache-control header.
     *
     * This considers several other headers and calculates or modifies the
     * cache-control header to a sensible, conservative value.
     */
    protected function computeCacheControlValue(): string
    {
        if (count($this->cacheControl) === 0) {
            if ($this->has('Last-Modified') || $this->has('Expires')) {
                return 'private, must-revalidate';
                // allows for heuristic expiration (RFC 7234 Section 4.2.2) in the case of "Last-Modified"
            }

            // conservative by default
            return 'no-cache, private';
        }

        $header = $this->getCacheControlHeader();
        if (isset($this->cacheControl['public']) || isset($this->cacheControl['private'])) {
            return $header;
        }

        // public if s-maxage is defined, private otherwise
        if (!isset($this->cacheControl['s-maxage'])) {
            return $header . ', private';
        }

        return $header;
    }

    private function initDate(): void
    {
        $this->set('Date', gmdate('D, d M Y H:i:s') . ' GMT');
    }
}
