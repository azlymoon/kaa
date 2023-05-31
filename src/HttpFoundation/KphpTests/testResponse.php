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

use Kaa\HttpFoundation\KphpTests\ResponseTest;

require __DIR__ . '/../../../vendor/autoload.php';

$test = new ResponseTest();

echo"\ntestToString\n";
$test->testToString();

echo"\ntestClone\n";
$test->testClone();

echo"\ntestSendHeaders\n";
$test->testSendHeaders();

echo"\ntestSend\n";
$test->testSend();

echo"\ntestGetCharset\n";
$test->testGetCharset();

echo"\ntestIsCacheable\n";
$test->testIsCacheable();

echo"\ntestIsCacheableWithErrorCode\n";
$test->testIsCacheableWithErrorCode();

echo"\ntestIsCacheableWithNoStoreDirective\n";
$test->testIsCacheableWithNoStoreDirective();

echo"\ntestMustRevalidate\n";
$test->testMustRevalidate();

echo"\ntestMustRevalidateWithMustRevalidateCacheControlHeader\n";
$test->testMustRevalidateWithMustRevalidateCacheControlHeader();

echo"\ntestMustRevalidateWithProxyRevalidateCacheControlHeader\n";
$test->testMustRevalidateWithProxyRevalidateCacheControlHeader();

echo"\ntestSetNotModified\n";
$test->testSetNotModified();

echo"\ntestIsSuccessful\n";
$test->testIsSuccessful();

echo"\ntestIsNotModified\n";
$test->testIsNotModified();

echo"\ntestIsNotModifiedNotSafe\n";
$test->testIsNotModifiedNotSafe();

echo"\ntestIsNotModifiedLastModified\n";
$test->testIsNotModifiedLastModified();

echo"\ntestIsNotModifiedEtag\n";
$test->testIsNotModifiedEtag();

echo"\ntestIsNotModifiedWeakEtag\n";
$test->testIsNotModifiedWeakEtag();

echo"\ntestIsNotModifiedLastModifiedAndEtag\n";
$test->testIsNotModifiedLastModifiedAndEtag();

echo"\ntestIsNotModifiedIfModifiedSinceAndEtagWithoutLastModified\n";
$test->testIsNotModifiedIfModifiedSinceAndEtagWithoutLastModified();

echo"\ntestIfNoneMatchWithoutETag\n";
$test->testIfNoneMatchWithoutETag();

echo"\ntestIsValidateable\n";
$test->testIsValidateable();

echo"\ntestGetDate\n";
$test->testGetDate();

echo"\ntestGetMaxAge\n";
$test->testGetMaxAge();

echo"\ntestSetSharedMaxAge\n";
$test->testSetSharedMaxAge();

echo"\ntestSetStaleIfError\n";
$test->testSetStaleIfError();

echo"\ntestSetStaleWhileRevalidate\n";
$test->testSetStaleWhileRevalidate();

echo"\ntestSetStaleIfErrorWithoutSharedMaxAge\n";
$test->testSetStaleIfErrorWithoutSharedMaxAge();

echo"\ntestSetStaleWhileRevalidateWithoutSharedMaxAge\n";
$test->testSetStaleWhileRevalidateWithoutSharedMaxAge();

echo"\ntestIsPrivate\n";
$test->testIsPrivate();

echo"\ntestExpire\n";
$test->testExpire();

echo"\ntestNullExpireHeader\n";
$test->testNullExpireHeader();

echo"\ntestGetTtl\n";
$test->testGetTtl();

echo"\ntestSetClientTtl\n";
$test->testSetClientTtl();

echo"\ntestGetSetProtocolVersion\n";
$test->testGetSetProtocolVersion();

echo"\ntestGetVary\n";
$test->testGetVary();

echo"\ntestSetVary\n";
$test->testSetVary();

echo"\ntestDefaultContentType\n";
$test->testDefaultContentType();

echo"\ntestContentTypeCharset\n";
$test->testContentTypeCharset();

echo"\ntestPrepareDoesNothingIfContentTypeIsSet\n";
$test->testPrepareDoesNothingIfContentTypeIsSet();

echo"\ntestPrepareDoesNothingIfRequestFormatIsNotDefined\n";
$test->testPrepareDoesNothingIfRequestFormatIsNotDefined();

echo"\ntestPrepareDoesNotSetContentTypeBasedOnRequestAcceptHeader\n";
$test->testPrepareDoesNotSetContentTypeBasedOnRequestAcceptHeader();

echo"\ntestPrepareSetContentType\n";
$test->testPrepareSetContentType();

echo"\ntestPrepareRemovesContentForHeadRequests\n";
$test->testPrepareRemovesContentForHeadRequests();

echo"\ntestPrepareRemovesContentForInformationalResponse\n";
$test->testPrepareRemovesContentForInformationalResponse();

echo"\ntestPrepareRemovesContentLength\n";
$test->testPrepareRemovesContentLength();

echo"\ntestPrepareSetsPragmaOnHttp10Only\n";
$test->testPrepareSetsPragmaOnHttp10Only();

echo"\ntestPrepareSetsCookiesSecure\n";
$test->testPrepareSetsCookiesSecure();

echo"\ntestSetCache\n";
$test->testSetCache();

echo"\ntestSendContent\n";
$test->testSendContent();

echo"\ntestSetPublic\n";
$test->testSetPublic();

echo"\ntestSetImmutable\n";
$test->testSetImmutable();

echo"\ntestIsImmutable\n";
$test->testIsImmutable();

echo"\ntestSetDate\n";
$test->testSetDate();

echo"\ntestSetDateWithImmutable\n";
$test->testSetDateWithImmutable();

echo"\ntestSetExpires\n";
$test->testSetExpires();

echo"\ntestSetExpiresWithImmutable\n";
$test->testSetExpiresWithImmutable();

echo"\ntestSetLastModified\n";
$test->testSetLastModified();

echo"\ntestSetLastModifiedWithImmutable\n";
$test->testSetLastModifiedWithImmutable();

echo"\ntestIsInvalid\n";
$test->testIsInvalid();

echo"\ntestSetStatusCode\n";
$test->testSetStatusCode();

echo"\ntestIsInformational\n";
$test->testIsInformational();

echo"\ntestIsRedirectRedirection\n";
$test->testIsRedirectRedirection();

echo"\ntestIsNotFound\n";
$test->testIsNotFound();

echo"\ntestIsEmpty\n";
$test->testIsEmpty();

echo"\ntestIsForbidden\n";
$test->testIsForbidden();

echo"\ntestIsServerOrClientError\n";
$test->testIsServerOrClientError();

echo"\ntestHasVary\n";
$test->testHasVary();

echo"\ntestSetEtag\n";
$test->testSetEtag();

echo"\ntestSetContent\n";
$test->testSetContent();

echo"\ntestSetContentSafe\n";
$test->testSetContentSafe();
