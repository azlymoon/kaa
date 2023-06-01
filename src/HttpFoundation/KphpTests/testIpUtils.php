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

use Kaa\HttpFoundation\KphpTests\IpUtilsTest;

require __DIR__ . '/../../../vendor/autoload.php';

$test = new IpUtilsTest();

echo"\ntestIpV4\n";
$test->testIpv4();

echo"\ntestIpV6\n";
$test->testIpv6();

echo"\ntestInvalidIpAddressesDoNotMatch\n";
$test->testInvalidIpAddressesDoNotMatch();

echo"\ntestAnonymize\n";
$test->testAnonymize();

echo"\ntestIp4SubnetMaskZero\n";
$test->testIp4SubnetMaskZero();
