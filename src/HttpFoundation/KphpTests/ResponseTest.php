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
use Kaa\HttpFoundation\Request;
use Kaa\HttpFoundation\Response;
class ResponseTest
{
    public function testToString()
    {
        $response = new Response();
        $response = explode("\r\n", $response);
        var_dump('HTTP/1.0 200 OK' === $response[0]);
        var_dump('Cache-Control: no-cache, private' === $response[1]);
    }

    public function testClone()
    {
        $response = new Response();
        $responseClone = clone $response;
        var_dump($response->headers->all() === $responseClone->headers->all());
    }

    # This test works only in KPHP
    public function testSendHeaders()
    {
        $response = new Response();
        $headers = $response->sendHeaders();
        var_dump($response === $headers);
    }

    # This test works only in KPHP
    public function testSend()
    {
        $response = new Response();
        $responseSend = $response->send();
        var_dump($response === $responseSend);
    }

    public function testGetCharset()
    {
        $response = new Response();
        $charsetOrigin = 'UTF-8';
        $response->setCharset($charsetOrigin);
        $charset = $response->getCharset();
        var_dump($charsetOrigin === $charset);
    }

    public function testIsCacheable()
    {
        $response = new Response();
        var_dump(false === $response->isCacheable());
    }

    public function testIsCacheableWithErrorCode()
    {
        $response = new Response('', 500);
        var_dump(false === $response->isCacheable());
    }

    public function testIsCacheableWithNoStoreDirective()
    {
        $response = new Response();
        $response->headers->set('cache-control', 'private');
        var_dump(false === $response->isCacheable());
    }

    public function testMustRevalidate()
    {
        $response = new Response();
        var_dump(false === $response->mustRevalidate());
    }

    public function testMustRevalidateWithMustRevalidateCacheControlHeader()
    {
        $response = new Response();
        $response->headers->set('cache-control', 'must-revalidate');

        var_dump(true === $response->mustRevalidate());
    }

    public function testMustRevalidateWithProxyRevalidateCacheControlHeader()
    {
        $response = new Response();
        $response->headers->set('cache-control', 'proxy-revalidate');

        var_dump(true === $response->mustRevalidate());
    }

    public function testSetNotModified()
    {
        $response = new Response('foo');
        $modified = $response->setNotModified();
        var_dump($response->headers->all() === $modified->headers->all());
        var_dump(304 === $modified->getStatusCode());

        ob_start();
        $modified->sendContent();
        $string = ob_get_clean();
        var_dump($string === '');
    }

    public function testIsSuccessful()
    {
        $response = new Response();
        var_dump(true === $response->isSuccessful());
    }

    public function testIsNotModified()
    {
        $response = new Response();
        $modified = $response->isNotModified(new Request());
        var_dump(false === $modified);
    }

    public function testIsNotModifiedNotSafe()
    {
        $request = Request::create('/homepage', 'POST');

        $response = new Response();
        var_dump(false === $response->isNotModified($request));
    }

    public function testIsNotModifiedLastModified()
    {
        $before = 'Sun, 25 Aug 2013 18:32:31 GMT';
        $modified = 'Sun, 25 Aug 2013 18:33:31 GMT';
        $after = 'Sun, 25 Aug 2013 19:33:31 GMT';

        $request = new Request();
        $request->headers->set('If-Modified-Since', $modified);

        $response = new Response();

        $response->headers->set('Last-Modified', $modified);
        var_dump(true === $response->isNotModified($request));

        $response->headers->set('Last-Modified', $before);
        var_dump(true === $response->isNotModified($request));

        $response->headers->set('Last-Modified', $after);
        var_dump(false === $response->isNotModified($request));

        $response->headers->set('Last-Modified', '');
        var_dump(false === $response->isNotModified($request));
    }

    public function testIsNotModifiedEtag()
    {
        $etagOne = 'randomly_generated_etag';
        $etagTwo = 'randomly_generated_etag_2';

        $request = new Request();
        $request->headers->set('If-None-Match', sprintf('%s, %s, %s', $etagOne, $etagTwo, 'etagThree'));

        $response = new Response();

        $response->headers->set('ETag', $etagOne);
        var_dump(true === $response->isNotModified($request));

        $response->headers->set('ETag', $etagTwo);
        var_dump(true === $response->isNotModified($request));

        $response->headers->set('ETag', '');
        var_dump(false === $response->isNotModified($request));

        // Test wildcard
        $request = new Request();
        $request->headers->set('If-None-Match', '*');

        $response->headers->set('ETag', $etagOne);
        var_dump(true === $response->isNotModified($request));
    }

    public function testIsNotModifiedWeakEtag()
    {
        $etag = 'randomly_generated_etag';
        $weakEtag = 'W/randomly_generated_etag';

        $request = new Request();
        $request->headers->set('If-None-Match', $etag);
        $response = new Response();

        $response->headers->set('ETag', $etag);
        var_dump(true === $response->isNotModified($request));

        $response->headers->set('ETag', $weakEtag);
        var_dump(true === $response->isNotModified($request));

        $request->headers->set('If-None-Match', $weakEtag);
        $response = new Response();

        $response->headers->set('ETag', $etag);
        var_dump(true === $response->isNotModified($request));

        $response->headers->set('ETag', $weakEtag);
        var_dump(true === $response->isNotModified($request));
    }

    public function testIsNotModifiedLastModifiedAndEtag()
    {
        $before = 'Sun, 25 Aug 2013 18:32:31 GMT';
        $modified = 'Sun, 25 Aug 2013 18:33:31 GMT';
        $after = 'Sun, 25 Aug 2013 19:33:31 GMT';
        $etag = 'randomly_generated_etag';

        $request = new Request();
        $request->headers->set('If-None-Match', sprintf('%s, %s', $etag, 'etagThree'));
        $request->headers->set('If-Modified-Since', $modified);

        $response = new Response();

        $response->headers->set('ETag', $etag);
        $response->headers->set('Last-Modified', $after);
        var_dump(true === $response->isNotModified($request));

        $response->headers->set('ETag', 'non-existent-etag');
        $response->headers->set('Last-Modified', $before);
        var_dump(false === $response->isNotModified($request));

        $response->headers->set('ETag', $etag);
        $response->headers->set('Last-Modified', $modified);
        var_dump(true === $response->isNotModified($request));
    }

    public function testIsNotModifiedIfModifiedSinceAndEtagWithoutLastModified()
    {
        $modified = 'Sun, 25 Aug 2013 18:33:31 GMT';
        $etag = 'randomly_generated_etag';

        $request = new Request();
        $request->headers->set('If-None-Match', sprintf('%s, %s', $etag, 'etagThree'));
        $request->headers->set('If-Modified-Since', $modified);

        $response = new Response();

        $response->headers->set('ETag', $etag);
        var_dump(true === $response->isNotModified($request));

        $response->headers->set('ETag', 'non-existent-etag');
        var_dump(false === $response->isNotModified($request));
    }

    public function testIfNoneMatchWithoutETag()
    {
        $request = new Request();
        $request->headers->set('If-None-Match', 'randomly_generated_etag');

        var_dump(false === (new Response())->isNotModified($request));

        // Test wildcard
        $request = new Request();
        $request->headers->set('If-None-Match', '*');

        var_dump(false === (new Response())->isNotModified($request));
    }

    public function testIsValidateable()
    {
        $lastModified = $this->createDateTimeOneHourAgo()->format(\DATE_RFC2822);
        $response = new Response('', 200, ['Last-Modified' => $lastModified]);
        var_dump(true === $response->isValidateable());

        $response = new Response('', 200, ['ETag' => ['"12345"']]);
        var_dump(true === $response->isValidateable());

        $response = new Response();
        var_dump(false === $response->isValidateable());
    }

    protected function createDateTimeOneHourAgo(): \DateTime
    {
        return $this->createDateTimeNow()->sub(new \DateInterval('PT1H'));
    }

    protected function createDateTimeNow(): \DateTime
    {
        $date = new \DateTime();

        return $date->setTimestamp(time());
    }

    public function testGetDate()
    {
        $oneHourAgo = $this->createDateTimeOneHourAgo();
        $response = new Response('', 200, ['Date' => $oneHourAgo->format(\DATE_RFC2822)]);
        $date = $response->getDate();
        var_dump($oneHourAgo->getTimestamp() === $date->getTimestamp());

        $response = new Response();
        $date = $response->getDate();
        var_dump(time() === $date->getTimestamp());

        $response = new Response('', 200, ['Date' => $this->createDateTimeOneHourAgo()->format(\DATE_RFC2822)]);
        $now = $this->createDateTimeNow();
        $response->headers->set('Date', $now->format(\DATE_RFC2822));
        $date = $response->getDate();
        var_dump($now->getTimestamp() === $date->getTimestamp());

        $response = new Response('', 200);
        $now = $this->createDateTimeNow();
        $response->headers->remove('Date');
        $date = $response->getDate();
        var_dump($now->getTimestamp() === $date->getTimestamp());
    }

    public function testGetMaxAge()
    {
        $response = new Response();
        $response->headers->set('Cache-Control', 's-maxage=600, max-age=0');
        var_dump(600 === $response->getMaxAge());

        $response = new Response();
        $response->headers->set('Cache-Control', 'max-age=600');
        var_dump(600 === $response->getMaxAge());

        $response = new Response();
        $response->headers->set('Cache-Control', 'must-revalidate');
        $response->headers->set('Expires', $this->createDateTimeOneHourLater()->format(\DATE_RFC2822));
        var_dump(3600 === $response->getMaxAge());

        $response = new Response();
        $response->headers->set('Expires', -1);
        var_dump(0 === $response->getMaxAge());

        $response = new Response();
        var_dump(null === $response->getMaxAge());
    }

    protected function createDateTimeOneHourLater(): \DateTime
    {
        return $this->createDateTimeNow()->add(new \DateInterval('PT1H'));
    }

    public function testSetSharedMaxAge()
    {
        $response = new Response();
        $response->setSharedMaxAge(20);

        $cacheControl = $response->headers->get('Cache-Control');
        var_dump('public, s-maxage=20' === $cacheControl);
    }

    public function testSetStaleIfError()
    {
        $response = new Response();
        $response->setSharedMaxAge(20);
        $response->setStaleIfError(86400);

        $cacheControl = $response->headers->get('Cache-Control');
        var_dump('public, s-maxage=20, stale-if-error=86400' === $cacheControl);
    }

    public function testSetStaleWhileRevalidate()
    {
        $response = new Response();
        $response->setSharedMaxAge(20);
        $response->setStaleWhileRevalidate(300);

        $cacheControl = $response->headers->get('Cache-Control');
        var_dump('public, s-maxage=20, stale-while-revalidate=300' === $cacheControl);
    }

    public function testSetStaleIfErrorWithoutSharedMaxAge()
    {
        $response = new Response();
        $response->setStaleIfError(86400);

        $cacheControl = $response->headers->get('Cache-Control');
        var_dump('stale-if-error=86400, private' === $cacheControl);
    }

    public function testSetStaleWhileRevalidateWithoutSharedMaxAge()
    {
        $response = new Response();
        $response->setStaleWhileRevalidate(300);

        $cacheControl = $response->headers->get('Cache-Control');
        var_dump('stale-while-revalidate=300, private' === $cacheControl);
    }

    public function testIsPrivate()
    {
        $response = new Response();
        $response->headers->set('Cache-Control', 'max-age=100');
        $response->setPrivate();
        var_dump('100' === $response->headers->getCacheControlDirective('max-age'));
        var_dump(true === $response->headers->getCacheControlDirective('private'));

        $response = new Response();
        $response->headers->set('Cache-Control', 'public, max-age=100');
        $response->setPrivate();
        var_dump('100' === $response->headers->getCacheControlDirective('max-age'));
        var_dump(true === $response->headers->getCacheControlDirective('private'));
        var_dump(false === $response->headers->hasCacheControlDirective('public'));
    }

    public function testExpire()
    {
        $response = new Response();
        $response->headers->set('Cache-Control', 'max-age=100');
        $response->expire();
        var_dump('100' === $response->headers->get('Age'));

        $response = new Response();
        $response->headers->set('Cache-Control', 'max-age=100, s-maxage=500');
        $response->expire();
        var_dump('500' === $response->headers->get('Age'));

        $response = new Response();
        $response->headers->set('Cache-Control', 'max-age=5, s-maxage=500');
        $response->headers->set('Age', '1000');
        $response->expire();
        var_dump('1000' === $response->headers->get('Age'));

        $response = new Response();
        $response->expire();
        var_dump(false === $response->headers->has('Age'));

        # This test works only in KPHP
        $response = new Response();
        $response->headers->set('Expires', -1);
        $response->expire();
        var_dump(null === $response->headers->get('Age'));

        $response = new Response();
        $response->headers->set('Expires', date(\DATE_RFC2822, time() + 600));
        $response->expire();
        var_dump(null === $response->headers->get('Expires'));
    }

    # This test works only in KPHP
    public function testNullExpireHeader()
    {
        $response = new Response(null, 200, ['Expires' => null]);
        var_dump(null === $response->getExpires());
    }

    public function testGetTtl()
    {
        $response = new Response();
        var_dump(null === $response->getTtl());

        $response = new Response();
        $response->headers->set('Expires', $this->createDateTimeOneHourLater()->format(\DATE_RFC2822));
        var_dump(3600 === $response->getTtl());

        $response = new Response();
        $response->headers->set('Expires', $this->createDateTimeOneHourAgo()->format(\DATE_RFC2822));
        var_dump(0 === $response->getTtl());

        $response = new Response();
        $response->headers->set('Expires', $response->getDate()->format(\DATE_RFC2822));
        $response->headers->set('Age', 0);
        var_dump(0 === $response->getTtl());

        $response = new Response();
        $response->headers->set('Cache-Control', 'max-age=60');
        var_dump(60 === $response->getTtl());
    }

    public function testSetClientTtl()
    {
        $response = new Response();
        $response->setClientTtl(10);

        var_dump($response->getMaxAge() === $response->getAge() + 10);
    }

    public function testGetSetProtocolVersion()
    {
        $response = new Response();

        var_dump('1.0' === $response->getProtocolVersion());

        $response->setProtocolVersion('1.1');

        var_dump('1.1' === $response->getProtocolVersion());
    }

    public function testGetVary()
    {
        $response = new Response();
        var_dump([] === $response->getVary());

        $response = new Response();
        $response->headers->set('Vary', 'Accept-Language');
        var_dump(['Accept-Language'] === $response->getVary());

        $response = new Response();
        $response->headers->set('Vary', 'Accept-Language User-Agent    X-Foo');
        var_dump(['Accept-Language', 'User-Agent', 'X-Foo'] === $response->getVary());

        $response = new Response();
        $response->headers->set('Vary', 'Accept-Language,User-Agent,    X-Foo');
        var_dump(['Accept-Language', 'User-Agent', 'X-Foo'] === $response->getVary());

        $vary = ['Accept-Language', 'User-Agent', 'X-foo'];

        $response = new Response();
        $response->headers->set('Vary', $vary);
        var_dump($vary === $response->getVary());

        $response = new Response();
        $response->headers->set('Vary', 'Accept-Language, User-Agent, X-foo');
        var_dump($vary === $response->getVary());
    }

    public function testSetVary()
    {
        $response = new Response();
        $response->setVary('Accept-Language');
        var_dump(['Accept-Language'] === $response->getVary());

        $response->setVary('Accept-Language, User-Agent');
        var_dump(['Accept-Language', 'User-Agent'] === $response->getVary());

        $response->setVary('X-Foo', false);
        var_dump(['Accept-Language', 'User-Agent', 'X-Foo'] === $response->getVary());
    }

    public function testDefaultContentType()
    {
        $response = new Response('foo');
        $response->prepare(new Request());

        var_dump('text/html; charset=UTF-8' === $response->headers->get('Content-Type'));
    }

    public function testContentTypeCharset()
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/css');

        // force fixContentType() to be called
        $response->prepare(new Request());

        var_dump('text/css; charset=UTF-8' === $response->headers->get('Content-Type'));
    }

    public function testPrepareDoesNothingIfContentTypeIsSet()
    {
        $response = new Response('foo');
        $response->headers->set('Content-Type', 'text/plain');

        $response->prepare(new Request());

        var_dump('text/plain; charset=UTF-8' === $response->headers->get('content-type'));
    }

    public function testPrepareDoesNothingIfRequestFormatIsNotDefined()
    {
        $response = new Response('foo');

        $response->prepare(new Request());

        var_dump('text/html; charset=UTF-8' === $response->headers->get('content-type'));
    }

    /**
     * Same URL cannot produce different Content-Type based on the value of the Accept header,
     * unless explicitly stated in the response object.
     */
    public function testPrepareDoesNotSetContentTypeBasedOnRequestAcceptHeader()
    {
        $response = new Response('foo');
        $request = Request::create('/');
        $request->headers->set('Accept', 'application/json');
        $response->prepare($request);

        var_dump('text/html; charset=UTF-8' === $response->headers->get('content-type'));
    }

    public function testPrepareSetContentType()
    {
        $response = new Response('foo');
        $request = Request::create('/');
        $request->setRequestFormat('json');

        $response->prepare($request);

        var_dump('application/json' === $response->headers->get('content-type'));
    }

    public function testPrepareRemovesContentForHeadRequests()
    {
        $response = new Response('foo');
        $request = Request::create('/', 'HEAD');

        $length = 12345;
        $response->headers->set('Content-Length', $length);
        $response->prepare($request);

        var_dump('' === $response->getContent());
        var_dump((string)$length === $response->headers->get('Content-Length'));
    }

    public function testPrepareRemovesContentForInformationalResponse()
    {
        $response = new Response('foo');
        $request = Request::create('/');

        $response->setContent('content');
        $response->setStatusCode(101);
        $response->prepare($request);
        var_dump('' === $response->getContent());
        var_dump(false === $response->headers->has('Content-Type'));

        $response->setContent('content');
        $response->setStatusCode(304);
        $response->prepare($request);
        var_dump('' === $response->getContent());
        var_dump(false === $response->headers->has('Content-Type'));
        var_dump(false === $response->headers->has('Content-Length'));
    }

    public function testPrepareRemovesContentLength()
    {
        $response = new Response('foo');
        $request = Request::create('/');

        $response->headers->set('Content-Length', 12345);
        $response->prepare($request);
        var_dump('12345' === $response->headers->get('Content-Length'));

        $response->headers->set('Transfer-Encoding', 'chunked');
        $response->prepare($request);
        var_dump(false === $response->headers->has('Content-Length'));
    }

    public function testPrepareSetsPragmaOnHttp10Only()
    {
        $request = Request::create('/', 'GET');
        $request->server->set('SERVER_PROTOCOL', 'HTTP/1.0');

        $response = new Response('foo');
        $response->prepare($request);
        var_dump('no-cache' === $response->headers->get('pragma'));
        var_dump('-1' === $response->headers->get('expires'));

        $response = new Response('foo');
        $response->headers->remove('cache-control');
        $response->prepare($request);
        var_dump(false === $response->headers->has('pragma'));
        var_dump(false === $response->headers->has('expires'));

        $request->server->set('SERVER_PROTOCOL', 'HTTP/1.1');
        $response = new Response('foo');
        $response->prepare($request);
        var_dump(false === $response->headers->has('pragma'));
        var_dump(false === $response->headers->has('expires'));
    }

    public function testPrepareSetsCookiesSecure()
    {
        $cookie = Cookie::create('foo', 'bar');

        $response = new Response('foo');
        $response->headers->setCookie($cookie);

        $request = Request::create('/', 'GET');
        $response->prepare($request);

        var_dump(false === $cookie->isSecure());

        $request = Request::create('https://localhost/', 'GET');
        $response->prepare($request);

        var_dump(true === $cookie->isSecure());
    }

    public function testSetCache()
    {
        $response = new Response();
        // ['etag', 'last_modified', 'max_age', 's_maxage', 'private', 'public']
        try {
            $response->setCache(['wrong option' => 'value']);
        } catch (\Exception $e) {
            var_dump($e instanceof \InvalidArgumentException);
            var_dump(stripos($e->getMessage(), '"wrong option"') !== false);
        }

        $options = ['etag' => '"whatever"'];
        $response->setCache($options);
        var_dump('"whatever"' === $response->getEtag());

        $now = $this->createDateTimeNow();
        $options = ['last_modified' => $now->getTimestamp()];
        $response->setCache($options);
        var_dump($now->getTimestamp() === $response->getLastModified()->getTimestamp());

        $options = ['max_age' => 100];
        $response->setCache($options);
        var_dump(100 === $response->getMaxAge());

        $options = ['s_maxage' => 200];
        $response->setCache($options);
        var_dump(200 === $response->getMaxAge());

        var_dump(true === $response->headers->hasCacheControlDirective('public'));
        var_dump(false === $response->headers->hasCacheControlDirective('private'));

        $response->setCache(['public' => true]);
        var_dump(true === $response->headers->hasCacheControlDirective('public'));
        var_dump(false === $response->headers->hasCacheControlDirective('private'));

        $response->setCache(['public' => false]);
        var_dump(false === $response->headers->hasCacheControlDirective('public'));
        var_dump(true === $response->headers->hasCacheControlDirective('private'));

        $response->setCache(['private' => true]);
        var_dump(false === $response->headers->hasCacheControlDirective('public'));
        var_dump(true === $response->headers->hasCacheControlDirective('private'));

        $response->setCache(['private' => false]);
        var_dump(true === $response->headers->hasCacheControlDirective('public'));
        var_dump(false === $response->headers->hasCacheControlDirective('private'));

        $response->setCache(['immutable' => true]);
        var_dump(true === $response->headers->hasCacheControlDirective('immutable'));

        $response->setCache(['immutable' => false]);
        var_dump(false === $response->headers->hasCacheControlDirective('immutable'));

        $directives = ['proxy_revalidate', 'must_revalidate', 'no_cache', 'no_store', 'no_transform'];
        foreach ($directives as $directive) {
            $response->setCache([$directive => true]);
            $count = 0;
            var_dump(true === $response->headers->hasCacheControlDirective(str_replace('_', '-', $directive, $count)));
        }

        foreach ($directives as $directive) {
            $response->setCache([$directive => false]);
            $count = 0;
            var_dump(false === $response->headers->hasCacheControlDirective(str_replace('_', '-', $directive, $count)));
        }

        $response = new DefaultResponse();

        $options = ['etag' => '"whatever"'];
        $response->setCache($options);
        var_dump($response->getEtag() === '"whatever"');
    }

    public function testSendContent()
    {
        $response = new Response('test response rendering', 200);

        ob_start();
        $response->sendContent();
        $string = ob_get_clean();
        var_dump(stripos($string, 'test response rendering') !== false);
    }

    public function testSetPublic()
    {
        $response = new Response();
        $response->setPublic();

        var_dump(true === $response->headers->hasCacheControlDirective('public'));
        var_dump(false === $response->headers->hasCacheControlDirective('private'));
    }

    public function testSetImmutable()
    {
        $response = new Response();
        $response->setImmutable();

        var_dump(true === $response->headers->hasCacheControlDirective('immutable'));
    }

    public function testIsImmutable()
    {
        $response = new Response();
        $response->setImmutable();

        var_dump(true === $response->isImmutable());
    }

    public function testSetDate()
    {
        $response = new Response();
        $response->setDate(\DateTime::createFromFormat(\DateTimeInterface::ATOM, '2013-01-26T09:21:56+0100', new \DateTimeZone('Etc/GMT-3')));

        var_dump('2013-01-26T08:21:56+00:00' === $response->getDate()->format(\DateTimeInterface::ATOM));
    }

    public function testSetDateWithImmutable()
    {
        $response = new Response();
        $response->setDate(\DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2013-01-26T09:21:56+0100', new \DateTimeZone('Europe/Moscow')));

        var_dump('2013-01-26T08:21:56+00:00' === $response->getDate()->format(\DateTimeInterface::ATOM));
    }

    public function testSetExpires()
    {
        $response = new Response();
        $response->setExpires(null);

        var_dump(null === $response->getExpires());

        $now = $this->createDateTimeNow();
        $response->setExpires($now);

        var_dump($response->getExpires()->getTimestamp() === $now->getTimestamp());
    }

    public function testSetExpiresWithImmutable()
    {
        $response = new Response();

        $now = $this->createDateTimeImmutableNow();
        $response->setExpires($now);

        var_dump($response->getExpires()->getTimestamp() === $now->getTimestamp());
    }

    protected function createDateTimeImmutableNow(): \DateTimeImmutable
    {
        $date = new \DateTimeImmutable();

        return $date->setTimestamp(time());
    }

    public function testSetLastModified()
    {
        $response = new Response();
        $response->setLastModified($this->createDateTimeNow());
        var_dump($response->getLastModified() !== null);

        $response->setLastModified(null);
        var_dump($response->getLastModified() === null);
    }

    public function testSetLastModifiedWithImmutable()
    {
        $response = new Response();
        $response->setLastModified($this->createDateTimeImmutableNow());
        var_dump($response->getLastModified() !== null);

        $response->setLastModified(null);
        var_dump($response->getLastModified() === null);
    }

    public function testIsInvalid()
    {
        $response = new Response();

        try {
            $response->setStatusCode(99);
        } catch (\InvalidArgumentException $e) {
            var_dump(true === $response->isInvalid());
        }

        try {
            $response->setStatusCode(650);
        } catch (\InvalidArgumentException $e) {
            var_dump(true === $response->isInvalid());
        }

        $response = new Response('', 200);
        var_dump(false === $response->isInvalid());
    }

    /**
     * @dataProvider getStatusCodeFixtures
     */
    public function testSetStatusCode()
    {
        $data = self::getStatusCodeFixtures();
        foreach ($data as $input) {
            $code = (int)$input[0];
            if ($input[1] === null) {
                $text = null;
            } else {
                $text = (string)$input[1];
            }
            $expectedText = (string)$input[2];

            $response = new Response();

            $response->setStatusCode($code, $text);

            var_dump($expectedText === $response->getStatusText());
        }
    }

    /** @return mixed[][] */
    public static function getStatusCodeFixtures()
    {
        return [
            ['200', null, 'OK'],
            ['200', false, ''],
            ['200', 'foo', 'foo'],
            ['199', null, 'unknown status'],
            ['199', false, ''],
            ['199', 'foo', 'foo'],
        ];
    }

    public function testIsInformational()
    {
        $response = new Response('', 100);
        var_dump(true === $response->isInformational());

        $response = new Response('', 200);
        var_dump(false === $response->isInformational());
    }

    public function testIsRedirectRedirection()
    {
        foreach ([301, 302, 303, 307] as $code) {
            $response = new Response('', $code);
            var_dump(true === $response->isRedirection());
            var_dump(true === $response->isRedirect());
        }

        $response = new Response('', 304);
        var_dump(true === $response->isRedirection());
        var_dump(false === $response->isRedirect());

        $response = new Response('', 200);
        var_dump(false === $response->isRedirection());
        var_dump(false === $response->isRedirect());

        $response = new Response('', 404);
        var_dump(false === $response->isRedirection());
        var_dump(false === $response->isRedirect());

        $response = new Response('', 301, ['Location' => '/good-uri']);
        var_dump(false === $response->isRedirect('/bad-uri'));
        var_dump(true === $response->isRedirect('/good-uri'));
    }

    public function testIsNotFound()
    {
        $response = new Response('', 404);
        var_dump(true === $response->isNotFound());

        $response = new Response('', 200);
        var_dump(false === $response->isNotFound());
    }

    public function testIsEmpty()
    {
        foreach ([204, 304] as $code) {
            $response = new Response('', $code);
            var_dump(true === $response->isEmpty());
        }

        $response = new Response('', 200);
        var_dump(false === $response->isEmpty());
    }

    public function testIsForbidden()
    {
        $response = new Response('', 403);
        var_dump(true === $response->isForbidden());

        $response = new Response('', 200);
        var_dump(false === $response->isForbidden());
    }

    public function testIsServerOrClientError()
    {
        $response = new Response('', 404);
        var_dump(true === $response->isClientError());
        var_dump(false === $response->isServerError());

        $response = new Response('', 500);
        var_dump(false === $response->isClientError());
        var_dump(true === $response->isServerError());
    }

    public function testHasVary()
    {
        $response = new Response();
        var_dump(false === $response->hasVary());

        $response->setVary('User-Agent');
        var_dump(true === $response->hasVary());
    }

    public function testSetEtag()
    {
        $response = new Response('', 200, ['ETag' => '"12345"']);
        $response->setEtag(null);

        var_dump(null === $response->headers->get('Etag'));
    }

    /**
     * @dataProvider validContentProvider
     */
    public function testSetContent()
    {
        $data = self::validContentProvider();
        foreach ($data as $input) {
            $content = $input[0];
            $response = new Response();
            $response->setContent((string)$content);
            var_dump((string) $content === $response->getContent());
        }

        $data = self::validContentStrObjProvider();
        foreach ($data as $input) {
            $content = $input[0];
            $response = new Response();
            $response->setContent((string)$content);
            var_dump((string) $content === $response->getContent());
        }
    }

    /** @return mixed[][] */
    public static function validContentProvider()
    {
        return [
            'string' => ['Foo'],
            'int' => [2],
        ];
    }

    /** @return StringableObject[][] */
    public static function validContentStrObjProvider()
    {
        return [
            'obj' => [new StringableObject()],
        ];
    }

    public function testSetContentSafe()
    {
        $response = new Response();

        var_dump(false === $response->headers->has('Preference-Applied'));
        var_dump(false === $response->headers->has('Vary'));

        $response->setContentSafe();

        var_dump('safe' === $response->headers->get('Preference-Applied'));
        var_dump('Prefer' === $response->headers->get('Vary'));

        $response->setContentSafe(false);

        var_dump(false === $response->headers->has('Preference-Applied'));
        var_dump('Prefer' === $response->headers->get('Vary'));
    }
}
