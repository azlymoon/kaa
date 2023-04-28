<?php

namespace Kaa\HttpFoundation;

/**
 * Represents a cookie.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Cookie
{
    public const SAMESITE_NONE = 'none';
    public const SAMESITE_LAX = 'lax';
    public const SAMESITE_STRICT = 'strict';


    /** @var string $name*/
    protected $name;

    /** @var string|null  */
    protected $value;

    /** @var string|null  */
    protected $domain;

    /** @var int|string|\DateTimeInterface  */
    protected $expire;

    /** @var string  */
    protected $path;

    /** @var bool|null  */
    protected $secure;

    /** @var bool  */
    protected $httpOnly;
    private ?string $sameSite = null;
    private const RESERVED_CHARS_LIST = "=,; \t\r\n\v\f";

    /**
     * @see self::__construct
     *
     * @param string                        $name     The name of the cookie
     * @param string|null                   $value    The value of the cookie
     * @param int|string|\DateTimeInterface $expire   The time the cookie expires
     * @param string                        $path     The path on the server in which the cookie will be available on
     * @param string|null                   $domain   The domain that the cookie is available to
     * @param bool|null                     $secure   Whether the client should send back the cookie only over HTTPS or null to auto-enable this when the request is already using HTTPS
     * @param bool                          $httpOnly Whether the cookie will be made accessible only through the HTTP protocol
     * @param bool                          $raw      Whether the cookie value should be sent with no url encoding
     * @param string|null                   $sameSite
     *
     */
    public static function create($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = null, bool $httpOnly = true, $raw = false, $sameSite = self::SAMESITE_LAX): self
    {
        return new self($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * @param string                        $name     The name of the cookie
     * @param string|null                   $value    The value of the cookie
     * @param int|string|\DateTimeInterface $expire   The time the cookie expires
     * @param string                        $path     The path on the server in which the cookie will be available on
     * @param string|null                   $domain   The domain that the cookie is available to
     * @param bool|null                     $secure   Whether the client should send back the cookie only over HTTPS or null to auto-enable this when the request is already using HTTPS
     * @param bool                          $httpOnly Whether the cookie will be made accessible only through the HTTP protocol
     * @param bool                          $raw      Whether the cookie value should be sent with no url encoding
     * @param string|null                   $sameSite
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = null, bool $httpOnly = true, $raw = false, $sameSite = self::SAMESITE_LAX)
    {
        // from PHP source code
        if ($raw && false !== strpbrk($name, self::RESERVED_CHARS_LIST)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if (empty($name)) {
            throw new \InvalidArgumentException('The cookie name cannot be empty.');
        }

        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->expire = self::expiresTimestamp($expire);
        $this->path = empty($path) ? '/' : $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->raw = $raw;
        $this->sameSite = $this->withSameSite($sameSite)->sameSite;
    }

    /**
     * Creates a cookie copy with a new value.
     */
    public function withValue(?string $value): static
    {
        $cookie = clone $this;
        $cookie->value = $value;

        return $cookie;
    }

    /**
     * TODO: пофиксить \DateTimeInterface
     * Converts expires formats to a unix timestamp.
     */
    private static function expiresTimestamp(int|string|\DateTimeInterface $expire = 0): int
    {
        // convert expiration time to a Unix timestamp
        if ($expire instanceof \DateTimeInterface) {
            $expire = $expire->format('U');
        } elseif (!is_numeric($expire)) {
            $expire = strtotime($expire);

            if (false === $expire) {
                throw new \InvalidArgumentException('The cookie expiration time is not valid.');
            }
        }

        return 0 < $expire ? (int) $expire : 0;
    }

    /**
     * Creates a cookie copy with SameSite attribute.
     */
    public function withSameSite(?string $sameSite): static
    {
        if ('' === $sameSite) {
            $sameSite = null;
        } elseif (null !== $sameSite) {
            $sameSite = strtolower($sameSite);
        }

        if (!\in_array($sameSite, [self::SAMESITE_LAX, self::SAMESITE_STRICT, self::SAMESITE_NONE, null], true)) {
            throw new \InvalidArgumentException('The "sameSite" parameter value is not valid.');
        }

        $cookie = clone $this;
        $cookie->sameSite = $sameSite;

        return $cookie;
    }

    /**
     * Gets the name of the cookie.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the domain that the cookie is available to.
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Gets the path on the server in which the cookie will be available on.
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
