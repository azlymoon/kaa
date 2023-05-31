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

use Kaa\HttpFoundation\KphpTests\ResponseHeaderBagTest;

require __DIR__ . '/../../../vendor/autoload.php';

$test = new ResponseHeaderBagTest();

echo"\ntestAllPreserveCase\n";
$test->testAllPreserveCase();

echo"\ntestCacheControlHeader\n";
$test->testCacheControlHeader();

echo"\ntestCacheControlClone\n";
$test->testCacheControlClone();

echo"\ntestToStringIncludesCookieHeaders\n";
$test->testToStringIncludesCookieHeaders();

echo"\ntestClearCookieSecureNotHttpOnly\n";
$test->testClearCookieSecureNotHttpOnly();

echo"\ntestClearCookieSamesite\n";
$test->testClearCookieSamesite();

echo"\ntestReplace\n";
$test->testReplace();

echo"\ntestReplaceWithRemove\n";
$test->testReplaceWithRemove();

echo"\ntestCookiesWithSameNames\n";
$test->testCookiesWithSameNames();

echo"\ntestRemoveCookie\n";
$test->testRemoveCookie();

echo"\ntestRemoveCookieWithNullRemove\n";
$test->testRemoveCookieWithNullRemove();

echo"\ntestSetCookieHeader\n";
$test->testSetCookieHeader();

echo"\ntestToStringDoesntMessUpHeaders\n";
$test->testToStringDoesntMessUpHeaders();

echo"\ntestDateHeaderAddedOnCreation\n";
$test->testDateHeaderAddedOnCreation();

echo"\ntestDateHeaderCanBeSetOnCreation\n";
$test->testDateHeaderCanBeSetOnCreation();

echo"\ntestDateHeaderWillBeRecreatedWhenRemoved\n";
$test->testDateHeaderWillBeRecreatedWhenRemoved();

echo"\ntestDateHeaderWillBeRecreatedWhenHeadersAreReplaced\n";
$test->testDateHeaderWillBeRecreatedWhenHeadersAreReplaced();
