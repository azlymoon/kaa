<?php

use Kaa\HttpFoundation\KphpTests\CookieTest;

require __DIR__ . '/../../../vendor/autoload.php';

$test = new CookieTest();

echo"\ntestInstantiationThrowsExceptionIfRawCookieNameContainsSpecialCharacters\n";
$test->testInstantiationThrowsExceptionIfRawCookieNameContainsSpecialCharacters();

echo"\ntestWithRawThrowsExceptionIfCookieNameContainsSpecialCharacters\n";
$test->testWithRawThrowsExceptionIfCookieNameContainsSpecialCharacters();

echo"\ntestInstantiationSucceedNonRawCookieNameContainsSpecialCharacters\n";
$test->testInstantiationSucceedNonRawCookieNameContainsSpecialCharacters();

echo"\ntestInstantiationThrowsExceptionIfCookieNameIsEmpty\n";
$test->testInstantiationThrowsExceptionIfCookieNameIsEmpty();

echo"\ntestInvalidExpiration\n";
$test->testInvalidExpiration();

echo"\ntestNegativeExpirationIsNotPossible\n";
$test->testNegativeExpirationIsNotPossible();

echo"\ntestGetValue\n";
$test->testGetValue();

echo"\ntestGetPath\n";
$test->testGetPath();

echo"\ntestGetExpiresTime\n";
$test->testGetExpiresTime();

echo"\ntestConstructorWithDateTime\n";
$test->testConstructorWithDateTime();

echo"\ntestConstructorWithDateTimeImmutable\n";
$test->testConstructorWithDateTimeImmutable();

echo"\ntestGetExpiresTimeWithStringValue\n";
$test->testGetExpiresTimeWithStringValue();

echo"\ntestGetDomain\n";
$test->testGetDomain();

echo"\ntestIsSecure\n";
$test->testIsSecure();

echo"\ntestIsHttpOnly\n";
$test->testIsHttpOnly();

echo"\ntestCookieIsNotCleared\n";
$test->testCookieIsNotCleared();

echo"\ntestCookieIsCleared\n";
$test->testCookieIsCleared();

echo"\ntestToString\n";
$test->testToString();

echo"\ntestRawCookie\n";
$test->testRawCookie();

echo"\ntestGetMaxAge\n";
$test->testGetMaxAge();

echo"\ntestFromString\n";
$test->testFromString();

echo"\ntestFromStringWithHttpOnly\n";
$test->testFromStringWithHttpOnly();

echo"\ntestSameSiteAttribute\n";
$test->testSameSiteAttribute();

echo"\ntestSetSecureDefault\n";
$test->testSetSecureDefault();

echo"\ntestMaxAge\n";
$test->testMaxAge();

echo"\ntestExpiredWithMaxAge\n";
$test->testExpiredWithMaxAge();
