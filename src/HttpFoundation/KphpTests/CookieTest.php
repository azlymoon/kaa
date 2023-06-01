<?php

/*
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * This file was rewritten from the Symfony package
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kaa\HttpFoundation\KphpTests;

use Kaa\HttpFoundation\Cookie;

/**
 * CookieTest.
 *
 * @author John Kary <john@johnkary.net>
 * @author Hugo Hamon <hugo.hamon@sensio.com>
 *
 * @group time-sensitive
 */
class CookieTest
{
    /** @return string[][] */
    public static function namesWithSpecialCharacters()
    {
        return [
            [',MyName'],
            [';MyName'],
            [' MyName'],
            ["\tMyName"],
            ["\rMyName"],
            ["\nMyName"],
            ["\013MyName"],
            ["\014MyName"],
        ];
    }

    /**
     * @dataProvider namesWithSpecialCharacters
     */
    public function testInstantiationThrowsExceptionIfRawCookieNameContainsSpecialCharacters()
    {
        $data = self::namesWithSpecialCharacters();
        foreach ($data as $input) {
            $name = $input[0];
            try {
                Cookie::create($name, null, 0, '', null, null, false, true);
                var_dump(false);
            } catch (\InvalidArgumentException $e) {
                var_dump(true);
            }
        }
    }

    /**
     * @dataProvider namesWithSpecialCharacters
     */
    public function testWithRawThrowsExceptionIfCookieNameContainsSpecialCharacters()
    {
        $data = self::namesWithSpecialCharacters();
        foreach ($data as $input) {
            $name = $input[0];
            try {
                Cookie::create($name)->withRaw();
                var_dump(false);
            } catch (\InvalidArgumentException $e) {
                var_dump(true);
            }
        }
    }

    /**
     * @dataProvider namesWithSpecialCharacters
     */
    public function testInstantiationSucceedNonRawCookieNameContainsSpecialCharacters()
    {
        $data = self::namesWithSpecialCharacters();
        foreach ($data as $input) {
            $name = $input[0];
            var_dump(Cookie::create($name) instanceof Cookie);
        }
    }

    public function testInstantiationThrowsExceptionIfCookieNameIsEmpty()
    {
        try {
            Cookie::create('');
        } catch (\InvalidArgumentException $e) {
            var_dump(true);
        }
    }

    public function testInvalidExpiration()
    {
        try {
            Cookie::create('MyCookie', 'foo', 'bar');
        } catch (\InvalidArgumentException $e) {
            var_dump(true);
        }
    }

    public function testNegativeExpirationIsNotPossible()
    {
        $cookie = Cookie::create('foo', 'bar', -100);

        var_dump(0 === $cookie->getExpiresTime());

        $cookie = Cookie::create('foo', 'bar')->withExpires(-100);

        var_dump(0 === $cookie->getExpiresTime());
    }

    public function testGetValue()
    {
        $value = 'MyValue';
        $cookie = Cookie::create('MyCookie', $value);

        var_dump($value === $cookie->getValue());
    }

    public function testGetPath()
    {
        $cookie = Cookie::create('foo', 'bar');

        var_dump('/' === $cookie->getPath());
    }

    public function testGetExpiresTime()
    {
        $cookie = Cookie::create('foo', 'bar');

        var_dump(0 === $cookie->getExpiresTime());

        $cookie = Cookie::create('foo', 'bar', $expire = time() + 3600);

        var_dump($expire === $cookie->getExpiresTime());

        $cookie = Cookie::create('foo')->withExpires($expire = time() + 3600);

        var_dump($expire === $cookie->getExpiresTime());
    }

    public function testConstructorWithDateTime()
    {
        $expire = new \DateTime();
        $cookie = Cookie::createWithExpiresDateTime('foo', 'bar', $expire);

        var_dump((int)$expire->format('U') === $cookie->getExpiresTime());

        $cookie = Cookie::create('foo')->withExpiresDateTime($expire);

        var_dump((int)$expire->format('U') === $cookie->getExpiresTime());
    }

    public function testConstructorWithDateTimeImmutable()
    {
        $expire = new \DateTimeImmutable();
        $cookie = Cookie::createWithExpiresDateTime('foo', 'bar', $expire);

        var_dump((int)$expire->format('U') === $cookie->getExpiresTime());

        $cookie = Cookie::create('foo')->withValue('bar')->withExpiresDateTime($expire);

        var_dump((int)$expire->format('U') === $cookie->getExpiresTime());
    }

    public function testGetExpiresTimeWithStringValue()
    {
        $value = '+1 day';
        $cookie = Cookie::create('foo', 'bar', $value);
        $expire = strtotime($value);

        var_dump($expire === $cookie->getExpiresTime());

        $cookie = Cookie::create('foo')->withValue('bar')->withExpires($value);

        var_dump($expire === $cookie->getExpiresTime());
    }

    public function testGetDomain()
    {
        $cookie = Cookie::create('foo', 'bar', 0, '/', '.myfoodomain.com');

        var_dump('.myfoodomain.com' === $cookie->getDomain());

        $cookie = Cookie::create('foo')->withDomain('.mybardomain.com');

        var_dump('.mybardomain.com' === $cookie->getDomain());
    }

    public function testIsSecure()
    {
        $cookie = Cookie::create('foo', 'bar', 0, '/', '.myfoodomain.com', true);

        var_dump(true === $cookie->isSecure());

        $cookie = Cookie::create('foo')->withSecure(true);

        var_dump(true === $cookie->isSecure());
    }

    public function testIsHttpOnly()
    {
        $cookie = Cookie::create('foo', 'bar', 0, '/', '.myfoodomain.com', false, true);

        var_dump(true === $cookie->isHttpOnly());

        $cookie = Cookie::create('foo')->withHttpOnly(true);

        var_dump(true === $cookie->isHttpOnly());
    }

    public function testCookieIsNotCleared()
    {
        $cookie = Cookie::create('foo', 'bar', time() + 3600 * 24);

        var_dump(false === $cookie->isCleared());

        $cookie = Cookie::create('foo')->withExpires(time() + 3600 * 24);

        var_dump(false === $cookie->isCleared());
    }

    public function testCookieIsCleared()
    {
        $cookie = Cookie::create('foo', 'bar', time() - 20);

        var_dump(true === $cookie->isCleared());

        $cookie = Cookie::create('foo')->withExpires(time() - 20);

        var_dump(true === $cookie->isCleared());

        $cookie = Cookie::create('foo', 'bar');

        var_dump(false === $cookie->isCleared());

        $cookie = Cookie::create('foo', 'bar');

        var_dump(false === $cookie->isCleared());

        $cookie = Cookie::create('foo', 'bar', -1);

        var_dump(false === $cookie->isCleared());

        $cookie = Cookie::create('foo')->withExpires(-1);

        var_dump(false === $cookie->isCleared());
    }

    public function testToString()
    {
        $expected = 'foo=bar; expires=Fri, 20 May 2011 15:25:52 GMT; Max-Age=0; path=/; domain=.myfoodomain.com; secure; httponly';
        $cookie = Cookie::create('foo', 'bar', $expire = strtotime('Fri, 20 May 2011 15:25:52 GMT'), '/', '.myfoodomain.com', true, true, false, null);
        var_dump($expected === (string) $cookie);

        $cookie = Cookie::create('foo')
            ->withValue('bar')
            ->withExpires(strtotime('Fri, 20 May 2011 15:25:52 GMT'))
            ->withDomain('.myfoodomain.com')
            ->withSecure(true)
            ->withSameSite(null);
        var_dump($expected === (string) $cookie);

        $expected = 'foo=bar%20with%20white%20spaces; expires=Fri, 20 May 2011 15:25:52 GMT; Max-Age=0; path=/; domain=.myfoodomain.com; secure; httponly';
        $cookie = Cookie::create('foo', 'bar with white spaces', strtotime('Fri, 20 May 2011 15:25:52 GMT'), '/', '.myfoodomain.com', true, true, false, null);
        var_dump($expected === (string) $cookie);

        $cookie = Cookie::create('foo')
            ->withValue('bar with white spaces')
            ->withExpires(strtotime('Fri, 20 May 2011 15:25:52 GMT'))
            ->withDomain('.myfoodomain.com')
            ->withSecure(true)
            ->withSameSite(null);
        var_dump($expected === (string) $cookie);

        $expected = 'foo=deleted; expires=' . gmdate('D, d M Y H:i:s T', $expire = time() - 31536001) . '; Max-Age=0; path=/admin/; domain=.myfoodomain.com; httponly';
        $cookie = Cookie::create('foo', null, 1, '/admin/', '.myfoodomain.com', false, true, false, null);
        var_dump($expected === (string) $cookie);

        $cookie = Cookie::create('foo')
            ->withExpires(1)
            ->withPath('/admin/')
            ->withDomain('.myfoodomain.com')
            ->withSameSite(null);
        var_dump($expected === (string) $cookie);

        $expected = 'foo=bar; path=/; httponly; samesite=lax';
        $cookie = Cookie::create('foo', 'bar');
        var_dump($expected === (string) $cookie);

        $cookie = Cookie::create('foo')->withValue('bar');
        var_dump($expected === (string) $cookie);
    }

    public function testRawCookie()
    {
        $cookie = Cookie::create('foo', 'b a r', 0, '/', null, false, false, false, null);
        var_dump(false === $cookie->isRaw());
        var_dump('foo=b%20a%20r; path=/' === (string) $cookie);

        $cookie = Cookie::create('test')->withValue('t e s t')->withHttpOnly(false)->withSameSite(null);
        var_dump(false === $cookie->isRaw());
        var_dump('test=t%20e%20s%20t; path=/' === (string) $cookie);

        $cookie = Cookie::create('foo', 'b+a+r', 0, '/', null, false, false, true, null);
        var_dump(true === $cookie->isRaw());
        var_dump('foo=b+a+r; path=/' === (string) $cookie);

        $cookie = Cookie::create('foo')
            ->withValue('t+e+s+t')
            ->withHttpOnly(false)
            ->withRaw(true)
            ->withSameSite(null);
        var_dump(true === $cookie->isRaw());
        var_dump('foo=t+e+s+t; path=/' === (string) $cookie);
    }

    public function testGetMaxAge()
    {
        $cookie = Cookie::create('foo', 'bar');
        var_dump(0 === $cookie->getMaxAge());

        $cookie = Cookie::create('foo', 'bar', $expire = time() + 100);
        var_dump($expire - time() === $cookie->getMaxAge());

        $cookie = Cookie::create('foo', 'bar', $expire = time() - 100);
        var_dump(0 === $cookie->getMaxAge());
    }

    public function testFromString()
    {
        $cookie = Cookie::fromString('foo=bar; expires=Fri, 20 May 2011 15:25:52 GMT; path=/; domain=.myfoodomain.com; secure; httponly');
        var_dump((string)Cookie::create('foo', 'bar', strtotime('Fri, 20 May 2011 15:25:52 GMT'), '/', '.myfoodomain.com', true, true, true, null) === (string)$cookie);

        $cookie = Cookie::fromString('foo=bar', true);
        var_dump((string)Cookie::create('foo', 'bar', 0, '/', null, false, false, false, null) === (string)$cookie);

        $cookie = Cookie::fromString('foo', true);
        var_dump((string)Cookie::create('foo', null, 0, '/', null, false, false, false, null) === (string)$cookie);

        $cookie = Cookie::fromString('foo_cookie=foo=1&bar=2&baz=3; expires=Tue, 22 Sep 2020 06:27:09 GMT; path=/');
        var_dump((string)Cookie::create('foo_cookie', 'foo=1&bar=2&baz=3', strtotime('Tue, 22 Sep 2020 06:27:09 GMT'), '/', null, false, false, true, null) === (string)$cookie);

        $cookie = Cookie::fromString('foo_cookie=foo==; expires=Tue, 22 Sep 2020 06:27:09 GMT; path=/');
        var_dump((string)Cookie::create('foo_cookie', 'foo==', strtotime('Tue, 22 Sep 2020 06:27:09 GMT'), '/', null, false, false, true, null) === (string)$cookie);
    }

    public function testFromStringWithHttpOnly()
    {
        $cookie = Cookie::fromString('foo=bar; expires=Fri, 20 May 2011 15:25:52 GMT; path=/; domain=.myfoodomain.com; secure; httponly');
        var_dump(true === $cookie->isHttpOnly());

        $cookie = Cookie::fromString('foo=bar; expires=Fri, 20 May 2011 15:25:52 GMT; path=/; domain=.myfoodomain.com; secure');
        var_dump(false === $cookie->isHttpOnly());
    }

    public function testSameSiteAttribute()
    {
        $cookie = new Cookie('foo', 'bar', 0, '/', null, false, true, false, 'Lax');
        var_dump('lax' === $cookie->getSameSite());

        $cookie = new Cookie('foo', 'bar', 0, '/', null, false, true, false, '');
        var_dump(null === $cookie->getSameSite());

        $cookie = Cookie::create('foo')->withSameSite('Lax');
        var_dump('lax' === $cookie->getSameSite());
    }

    public function testSetSecureDefault()
    {
        $cookie = Cookie::create('foo', 'bar');

        var_dump(false === $cookie->isSecure());

        $cookie->setSecureDefault(true);

        var_dump(true === $cookie->isSecure());

        $cookie->setSecureDefault(false);

        var_dump(false === $cookie->isSecure());
    }

    public function testMaxAge()
    {
        $futureDateOneHour = gmdate('D, d M Y H:i:s T', time() + 3600);

        $cookie = Cookie::fromString('foo=bar; Max-Age=3600; path=/');
        var_dump('foo=bar; expires=' . $futureDateOneHour . '; Max-Age=3600; path=/' === $cookie->__toString());

        $cookie = Cookie::fromString('foo=bar; expires=' . $futureDateOneHour . '; Max-Age=3600; path=/');
        var_dump('foo=bar; expires=' . $futureDateOneHour . '; Max-Age=3600; path=/' === $cookie->__toString());

        $futureDateHalfHour = gmdate('D, d M Y H:i:s T', time() + 1800);

        // Max-Age value takes precedence before expires
        $cookie = Cookie::fromString('foo=bar; expires=' . $futureDateHalfHour . '; Max-Age=3600; path=/');
        var_dump('foo=bar; expires=' . $futureDateOneHour . '; Max-Age=3600; path=/' === $cookie->__toString());
    }

    public function testExpiredWithMaxAge()
    {
        $cookie = Cookie::fromString('foo=bar; expires=Fri, 20 May 2011 15:25:52 GMT; Max-Age=0; path=/');
        var_dump('foo=bar; expires=Fri, 20 May 2011 15:25:52 GMT; Max-Age=0; path=/' === $cookie->__toString());

        $futureDate = gmdate('D, d-M-Y H:i:s T', time() + 864000);

        $cookie = Cookie::fromString('foo=bar; expires=' . $futureDate . '; Max-Age=0; path=/');
        var_dump(time() === $cookie->getExpiresTime());
        var_dump('foo=bar; expires=' . gmdate('D, d M Y H:i:s T', $cookie->getExpiresTime()) . '; Max-Age=0; path=/' === $cookie->__toString());
    }
}
