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

use Kaa\HttpFoundation\RedirectResponse;

class RedirectResponseTest
{
    public function testGenerateMetaRedirect()
    {
        $response = new RedirectResponse('foo.bar');

        var_dump(preg_match('#<meta http-equiv="refresh" content="\d+;url=\'foo\.bar\'" />#', preg_replace('/\s+/', ' ', $response->getContent())) !== false);
    }

    public function testRedirectResponseConstructorEmptyUrl()
    {
        try {
            new RedirectResponse('');
            var_dump(false);
        } catch (\InvalidArgumentException $e) {
            var_dump($e->getMessage() === 'Cannot redirect to an empty URL.');
        }
    }

    public function testRedirectResponseConstructorWrongStatusCode()
    {
        try {
            new RedirectResponse('foo.bar', 404);
            var_dump(false);
        } catch (\InvalidArgumentException $e) {
            var_dump(true);
        }
    }

    public function testGenerateLocationHeader()
    {
        $response = new RedirectResponse('foo.bar');

        var_dump(true === $response->headers->has('Location'));
        var_dump('foo.bar' === $response->headers->get('Location'));
    }

    public function testGetTargetUrl()
    {
        $response = new RedirectResponse('foo.bar');

        var_dump('foo.bar' === $response->getTargetUrl());
    }

    public function testSetTargetUrl()
    {
        $response = new RedirectResponse('foo.bar');
        $response->setTargetUrl('baz.beep');

        var_dump('baz.beep' === $response->getTargetUrl());
    }

    public function testCacheHeaders()
    {
        $response = new RedirectResponse('foo.bar', 301);
        var_dump(false === $response->headers->hasCacheControlDirective('no-cache'));

        $response = new RedirectResponse('foo.bar', 301, ['cache-control' => 'max-age=86400']);
        var_dump(false === $response->headers->hasCacheControlDirective('no-cache'));
        var_dump(true === $response->headers->hasCacheControlDirective('max-age'));

        $response = new RedirectResponse('foo.bar', 301, ['Cache-Control' => 'max-age=86400']);
        var_dump(false === $response->headers->hasCacheControlDirective('no-cache'));
        var_dump(true === $response->headers->hasCacheControlDirective('max-age'));

        $response = new RedirectResponse('foo.bar', 302);
        var_dump(true === $response->headers->hasCacheControlDirective('no-cache'));
    }
}
