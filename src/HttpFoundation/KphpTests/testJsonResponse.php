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

use Kaa\HttpFoundation\KphpTests\JsonResponseTest;

require __DIR__ . '/../../../vendor/autoload.php';

$test = new JsonResponseTest();

echo"\ntestConstructorEmptyCreatesJsonObject\n";
$test->testConstructorEmptyCreatesJsonObject();

echo"\ntestConstructorWithArrayCreatesJsonArray\n";
$test->testConstructorWithArrayCreatesJsonArray();

echo"\ntestConstructorWithAssocArrayCreatesJsonObject\n";
$test->testConstructorWithAssocArrayCreatesJsonObject();

echo"\ntestConstructorWithSimpleTypes\n";
$test->testConstructorWithSimpleTypes();

echo"\ntestConstructorWithCustomStatus\n";
$test->testConstructorWithCustomStatus();

echo"\ntestConstructorAddsContentTypeHeader\n";
$test->testConstructorAddsContentTypeHeader();

echo"\ntestConstructorWithCustomHeaders\n";
$test->testConstructorWithCustomHeaders();

echo"\ntestConstructorWithCustomContentType\n";
$test->testConstructorWithCustomContentType();

echo"\ntestSetJson\n";
$test->testSetJson();

echo"\ntestSetCallback\n";
$test->testSetCallback();

echo"\ntestJsonEncodeFlags\n";
$test->testJsonEncodeFlags();

echo"\ntestGetEncodingOptions\n";
$test->testGetEncodingOptions();

echo"\ntestItAcceptsJsonAsString\n";
$test->testItAcceptsJsonAsString();

echo"\ntestSetCallbackInvalidIdentifier\n";
$test->testSetCallbackInvalidIdentifier();

echo"\ntestSetComplexCallback\n";
$test->testSetComplexCallback();

echo"\ntestConstructorWithNullAsDataThrowsAnUnexpectedValueException\n";
$test->testConstructorWithNullAsDataThrowsAnUnexpectedValueException();

echo"\ntestConstructorWithObjectWithToStringMethod\n";
$test->testConstructorWithObjectWithToStringMethod();

echo"\ntestConstructorWithObject\n";
$test->testConstructorWithObject();
