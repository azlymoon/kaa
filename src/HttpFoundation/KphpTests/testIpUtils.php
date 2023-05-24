<?php

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
